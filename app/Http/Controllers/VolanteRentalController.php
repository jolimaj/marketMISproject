<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\Volante;
use App\Models\User;
use App\Models\StallRental;
use App\Models\StallType;
use App\Models\StallCategory;
use App\Models\FeeMasterlist;
use App\Models\RequirementChecklist;
use App\Models\Stall;
use App\Models\Permits;

use App\Http\Requests\VolanteRentalRequest;

use App\Http\Controllers\PermitController;
use App\Http\Controllers\RequirementController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\ApprovalPermitController;
use App\Models\StallsCategories;

use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class VolanteRentalController extends Controller
{

    public function index(Request $request): Response
    {   
        $user =  Auth::user();
        $data = [
            'filters' => $request->all('search', 'category'),
            'volanteRentals' => $this->getIndexData($request),
            'user' => $user,
            'stallsCategories' => StallsCategories::whereIn('id', [9, 10, 11])->get(),
        ];
        return Inertia::render($user->role_id === 3 ? 'Users/Applications/VolanteRental'
        : 'Admin/Applications/VolanteRental', $data);
    }

    public function show(Request $request, Volante $volanteRental): RedirectResponse
    {
        $user = Auth::user();

        // Only allow access if user is role 2 and permit is assigned to their department
        if ($user->role_id === 2 && $volanteRental->permits && $volanteRental->permits->department_id == $user->department_id ||
        $user->role_id === 1) {
            return Inertia::render('Admin/Applications/ShowVolanteRental', [
                'volanteRental' => $this->getSingleData($volanteRental),
            ]);
        }

        // Otherwise, redirect or abort
        return redirect()->route('department.applications.volantes')->with('error', 'Unauthorized access.');
    }

    public function edit(Volante $volanteRental): Response|RedirectResponse
    {
        $user = Auth::user();
        if ($user->role_id !== 3 && $volanteRental->status === 1) {
            return redirect()->route('department.applications.volantes');
        }

        
        return Inertia::render('Users/Applications/Volante/Form', array_merge(
                ['volanteRental' => $this->getSingleData($volanteRental)],
                $this->getCreatePayload()
        ));
    }

    public function fieldValidator(VolanteRentalRequest $request)
    {
        
        return Inertia::render('Users/Applications/Volante/Form',collect($this->getCreatePayload())
            ->merge(['paymentDetails' => $paymentDetails])
            ->toArray());
    }

    public function store(VolanteRentalRequest $request)
    {
        $payload = $request->validated();

        // Step 1 → calculate payment
        if (isset($payload['step']) && $payload['step'] == 1) {
            $stall = Stall::with('stallsCategories')->findOrFail((int) $payload['stall_id']);
            $feeMasterlist = FeeMasterlist::whereIn(
                'id',
                json_decode($stall->stallsCategories->fee_masterlist_ids, true)
            )->get();

            $paymentDetails = $this->calculateVolantePaymentFromFees(
                $payload['started_date'],
                $payload['end_date'],
                $payload['duration'] ?? 0,
                $payload['quantity'] ?? 0,
                $feeMasterlist->toArray()
            );

            // Merge into existing props (no redirect)
            return Inertia::render('Users/Applications/Volante/Form', collect($this->getCreatePayload())
            ->merge(['paymentDetails' => $paymentDetails])
            ->toArray())->withViewData(['step' => 1]);
        }

        // Step 2 → just return current props, no changes
        if (isset($payload['step']) && $payload['step'] == 2) {
            return Inertia::render('Users/Applications/Volante/Form')
                ->withViewData(['step' => 2]);
        }

        // Step 3 → final save
        if (isset($payload['step']) && $payload['step'] == 3) {
            $this->addData($payload);

            return redirect()
                ->route('applications.volantes.index')
                ->with('success', 'Volante rental application created successfully.');
        }

        // Default fallback
        return back()->withErrors(['step' => 'Invalid step provided.']);
    }


    public function create(): Response
    {
        return Inertia::render('Users/Applications/Volante/Form', $this->getCreatePayload());
    }

    public function update(VolanteRentalRequest $request, Volante $volanteRental) : RedirectResponse
    {
        $this->updateData($volanteRental, $request->all());
        return redirect()->route('applications.volantes.index')->with('success', 'Volante rental application updated successfully.');
    }

    public function approveVolante(Request $request, Volante $volanteRental) : RedirectResponse
    {
        $rentalDetails = $this->getSingleData($volanteRental);
        $approvalDetails = $this->approve($rentalDetails);
        return redirect()->back()->with('success', $approvalDetails['message'] ?? 'Volante rental approved successfully.');
    }

    public function rejectVolante(Request $request, Volante $volanteRental) : RedirectResponse
    {
        $request->validate([
            'remarks' => 'required|string|max:255',
        ]);
        $this->disApprove($volanteRental, $request->all());
        return redirect()->route('department.applications.volantes')->with('success', 'Volante rental rejected.');
    }
    
    public function indexApi(Request $request)
    {
        // $user = Auth::user();
        $user = [
            'id'=> 3,
            'name' => 'John Doe',
            'role_id' => 3
        ];
       
        Log::info('User: ', ['user' => $user]);
        $volanteRentals = $this->getIndexData($request);

        return response()->json([
            'volanteRentals' => $volanteRentals,
        ]);

    }

    public function editApi(Volante $volanteRental)
    {
      return response()->json(
            array_merge(
                ['volanteRental' => $this->getSingleData($volanteRental)],
                $this->getCreatePayload()
            )
        );

    }

    public function createApi()
    {
        return response()->json($this->getCreatePayload());

    }

    public function storeApi(VolanteRentalRequest $request)
    {  
        $volanteRental = $this->addData($request->validated());
        return response()->json(['data' => $volanteRental ]);
    }

    public function updateApi(VolanteRentalRequest $request, Volante $volanteRental)
    {
        Log::info('Update hit', [
            'volante_id' => $volanteRental->id,
            'request_all' => $request
        ]);
        return response()->json(
            array_merge(
                ['volanteRental' => $this->updateData($volanteRental, $request->all())],
            )
        );
    }

    public function approveApi(Volante $volanteRental)
    {
        $rentalDetails = $this->getSingleData($volanteRental);
        $approvalDetails = $this->approve($rentalDetails);
        return response()->json(['message' =>  $approvalDetails]);
    }

    public function rejectApi(VolanteRentalRequest $request, Volante $volanteRental)
    {
        $request->validate([
            'remarks' => 'required|string|max:255',
        ]);
        $this->disApprove($volanteRental, $request->all());
        return response()->json(['message' => 'Volante rental rejected.', 'remarks' => $request->remarks]);
    }

    public function countMonthlyApplicants(){
        $rawCounts = Volante::selectRaw('EXTRACT(MONTH FROM created_at) AS month, COUNT(*) AS total')
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy(DB::raw('EXTRACT(MONTH FROM created_at)'))
            ->pluck('total', 'month');

        return collect(range(1, 12))->map(function ($month) use ($rawCounts) {
            return [
                'month' => Carbon::create()->month($month)->format('M'), // e.g. 'Jan', 'Feb'
                'total' => $rawCounts->get($month, 0)
            ];
        });
    }

    public function countTodayApplicants(){
        return Volante::query()->totalTodayApplicants();
    }

    private function disApprove($volanteRental, array $payload) {
        $this->updateData($volanteRental, ['status' => 3]);
        $PermitController = new PermitController();
        $PermitController->update([
                'status' => 2, //rejected
                'remarks' => $payload['remarks'],
                'assign_to' => 1, // 1 = user
                'is_initial' => false, // set to false when forwarded to user
            ], $volanteRental->permit_id);
    }

    private function approve($volanteRental) {
        $user = Auth::user();
        $permit = (object)$volanteRental['permits'];

        $currentDeptId = $permit->department_id; // Approver's department
        // PMO->MTO->PMO
        $approvalPermitController = new ApprovalPermitController();
        $approvalPermitController->storeApproval(
            $permit->id, 
            $currentDeptId,
            $currentDeptId,
            $user->id
        );
        $permitController = new PermitController();

        // STEP 1: Initial → Send to MTO
        if ($permit->is_initial) {
            $permitController->update([
                'department_id' => 2,
                'assign_to' => 2,
                'is_initial' => false
            ], $permit->id);
           
            return response()->json(['message' => 'Initial approval done. Sent to MTO.']);
        }

        // STEP 2: MTO → Send to Market
        if ($currentDeptId === 2 && $permit->status == 0) {
             $permitController->update([
                'department_id' => 12,
            ], $permit->id);

            return response()->json(['message' => 'MTO approved. Sent to Office of the Mayor.']);
        }

        // STEP 3: Office of the Mayor → Send to Market
        if ($currentDeptId === 12 && $permit->status == 0) {
             $permitController->update([
                'department_id' => 10,
            ], $permit->id);

            return response()->json(['message' => 'Office of the Mayor approved. Sent to Public Market.']);
        }

        // STEP 4: Market → Final Approval
        if ($currentDeptId == 10 && $permit->status == 0) {
            
            $requiredDepartments = [
                2,
                10,
                12,
            ];

            $approvedDepartments = ApprovalPermit::where('permit_id', $permit->id)
                ->whereIn('department_id', $requiredDepartments)
                ->where('status', 1)
                ->distinct()
                ->pluck('department_id')
                ->toArray();

            if (count($approvedDepartments) < count($requiredDepartments)) {
                return response()->json(['message' => 'Cannot finalize: Missing required department approvals.'], 400);
            }

            // Finalize approval
            $this->permitController->update([
                'status'        => 1, // Approved
                'issued_date'   => now()->format('Y-m-d'),
                'assign_to'     => 1,
                'permit_number' => $permit->generatePermitNumber(10),
                'department_id' => null
            ], $permit->id);

            $this->updateData($volanteRental, ['status' => 1]);

            $stallController = new StallsListController();
            // Update stall status to 'assigned' (3)
            $stallController->statusUpdate(
                new Request(['status' => 1]),
                Stall::findOrFail((int) $volanteRental->stall_id)
            );
        }
        return [
            'message' => 'Volante rental approved successfully.',
            'volanteRental' => $volanteRental,
            'permit' => $permit
        ];

    }

    private function calculateVolantePaymentFromFees(
    string $startDate,
    string $endDate,
    int $quantity,
    int $durationType, // 1 = daily, 2 = weekly, 3 = event
    array $fees
    ) {
        $days = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $weeks = ceil($days / 7);

        $total = 0;
        $feeBreakdown = [];

        foreach ($fees as $index => $fee) {
            $amount = (float)$fee['amount'];
            $feeAmount = 0;

            if ($durationType === 3) { // event - one time fee * quantity
                // If fee is daily/weekly it doesn't matter, event is one time
                $feeAmount = $amount * $quantity;
            } else {
                // daily or weekly duration type calculations
                if (!empty($fee['is_styro']) && !empty($fee['is_daily'])) {
                    $multiplier = $durationType === 2 ? 7 * $weeks : $days;
                    $feeAmount = $amount * $quantity * $multiplier;

                } elseif (!empty($fee['is_per_kilo']) && !empty($fee['is_daily'])) {
                    $multiplier = $durationType === 2 ? 7 * $weeks : $days;
                    $feeAmount = $amount * $quantity * $multiplier;

                } elseif (!empty($fee['is_daily'])) {
                    $multiplier = $durationType === 2 ? 7 * $weeks : $days;
                    $feeAmount = $amount * $quantity * $multiplier;

                } else {
                    // One-time or other fee (no multiplication by duration or quantity)
                    $feeAmount = $amount;
                }
            }

            $feeBreakdown[] = [
                'type' => $fee['type'],
                'amount' => $feeAmount,
            ];

            $total += $feeAmount;
        }

        return [
            'days' => $days,
            'weeks' => $weeks,
            'breakdown' => $feeBreakdown,
            'total' => $total,
        ];
    }

    private function getIndexData(Request $request) {
        $user = Auth::user();

        $query = Volante::query()->filter($request->only('search', 'category'));

        if ($user && $user->role_id === 3) {
            $query->myApplication($user->id);
        }

        if ($user && $user->role_id === 2) {
            $query->forApproval($user->department_id);
        }

        return $query->paginate(10)
            ->through(function ($data) use ($user) {
                $stall = $data->stalls;
                $stallCategory = $stall ? $stall->stallsCategories : null;
                $permits = $data->permits;
                $feeMasterlist = $stallCategory ? FeeMasterlist::whereIn(
                    'id',
                    json_decode($stallCategory->fee_masterlist_ids, true)
                )->get() : collect();

                $paymentDetails = $this->calculateVolantePaymentFromFees(
                    $data->started_date,
                    $data->end_date,
                    $data->quantity,
                    $data->duration,
                    $feeMasterlist->toArray()
                );

                return [
                    'id' => $data->id,
                    'name' => $data->business_name,
                    'location' => $data->location,
                    'duration' => $data->duration,
                    'status' => $data->status,
                    'area_of_sqr_meter' => $data->area_of_sqr_meter,
                    'start_date' => Carbon::parse($data->started_date),
                    'end_date' => Carbon::parse($data->end_date),
                    'stalls' => $stall ? [
                        'id' => $stall->id,
                        'name' => $stall->name,
                        'size' => $stall->size,
                        'status' => $stall->status,
                        'area_of_sqr_meter' => $stall->area_of_sqr_meter,
                        'stall_category_id' => $stall->stall_category_id,
                        'stallsCategories' => $stallCategory ? [
                            'id' => $stallCategory->id,
                            'name' => $stallCategory->name,
                            'description' => $stallCategory->description,
                            'is_transient' => $stallCategory->is_transient,
                            'fee_masterlist_ids' => $stallCategory->fee_masterlist_ids,
                            'fee' => FeeMasterlist::whereIn(
                                'id',
                                json_decode($stallCategory->fee_masterlist_ids, true)
                            )->get(),
                        ] : null,
                    ] : null,
                    'permits' => $permits ? [
                        'id' => $permits->id,
                        'permit_number' => $permits->permit_number,
                         'type' => $permits->type === 1 ? 'New' : 'Renewal',
                        'status' => $permits->status,
                        'created_at' => $permits->created_at ? Carbon::parse($permits->created_at) : null,
                        'issued_date' => $permits->issued_date ? Carbon::parse($permits->issued_date) : null,
                        'valid_until' => $permits->valid_until ? Carbon::parse($permits->valid_until) : null,
                    ] : null,
                    'requirements' => $permits ? $permits->requirements->map(function ($requirement) {
                        return [
                            'id' => $requirement->id,
                            'name' => $requirement->requirementDetails->name,
                            'attachment' => $requirement->attachment,
                            'checklist_id' => $requirement->requirement_checklist_id,
                            'url' => asset('storage/uploads' . $requirement->path),
                        ];
                    }) : collect(),
                    'payments' => $paymentDetails,
                    'paymentRecord' => $data->payments,
                    'user' => $data->user,
                ];
            })
            ->withQueryString();
    }

    private function getSingleData(Volante $volanteRental) {
        $user = Auth::user();
        Log::info('VolanteRental:', [
            'volanteRental' => $volanteRental->permits->department_id,
            'department_id' => $user->department_id]
        );
        if ($user->role_id === 3 && $volanteRental->user_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        } elseif ($user->role_id === 2 &&  $volanteRental->permits && $volanteRental->permits->assign_to !== $user->department_id) {
            abort(403, 'Unauthorized access.');
        }
        $stall = $volanteRental->stalls;
        $stallCategory = $stall ? $stall->stallsCategories : null;
        $permits = $volanteRental->permits;
        $feeMasterlist = $stallCategory ? FeeMasterlist::whereIn(
            'id',
            json_decode($stallCategory->fee_masterlist_ids, true)
        )->get() : collect();

        return [
            'id' => $volanteRental->id,
            'name' => $volanteRental->business_name,
            'location' => $volanteRental->location,
            'duration' => $volanteRental->duration,
            'quantity' => $volanteRental->quantity,
            'status' => $volanteRental->status,
            'area_of_sqr_meter' => $volanteRental->area_of_sqr_meter,
            'start_date' => Carbon::parse($volanteRental->started_date),
            'end_date' => Carbon::parse($volanteRental->end_date),
            'stalls' => $stall ? [
                'id' => $stall->id,
                'name' => $stall->name,
                'size' => $stall->size,
                'status' => $stall->status,
                'area_of_sqr_meter' => $stall->area_of_sqr_meter,
                'stall_category_id' => $stall->stall_category_id,
                'stallsCategories' => $stallCategory ? [
                    'id' => $stallCategory->id,
                    'name' => $stallCategory->name,
                    'description' => $stallCategory->description,
                    'is_transient' => $stallCategory->is_transient,
                    'fee_masterlist_ids' => $stallCategory->fee_masterlist_ids,
                    'fee' => FeeMasterlist::whereIn(            
                        'id',
                        json_decode($stallCategory->fee_masterlist_ids, true)
                    )->get(),
                ] : null,
            ] : null,
            'permits' => $permits ? [
                'id' => $permits->id,
                'type' => $permits->type,
                'permit_number' => $permits->permit_number,
                'department_id' => $permits->department_id,
                'status' => $permits->status,
                'remarks' => $permits->remarks,
                'assign_to' => $permits->assign_to,
                'is_initial' => $permits->is_initial,
                'issued_date' => $permits->issued_date ? Carbon::parse($permits->issued_date) : null,
                'valid_until' => $permits->valid_until ? Carbon::parse($permits->valid_until) : null,
            ] : null,
            'requirements' => $permits ? $permits->requirements->map(function ($requirement) {
                return [
                    'id' => $requirement->id,
                    'attachment' => $requirement->attachment,   
                    'checklist_id' => $requirement->requirement_checklist_id,
                    'url' => asset('storage/uploads' . $requirement->path),
                ];
            }) : collect(),
            'payments' => $this->calculateVolantePaymentFromFees(
                $volanteRental->started_date,
                $volanteRental->end_date,
                $volanteRental->quantity,
                $volanteRental->duration,
                $feeMasterlist->toArray()
            ),
            'paymentRecord' => $volanteRental->payments,
            'user' => $volanteRental->user,
        ];  
    }

    private function addData(array $payload)
    {
        $user = Auth::user();

        $requirementController = new RequirementController();
        $paymentController = new PaymentsController();
        $stallController = new StallsListController();
        $permitController = new PermitController();

        // Payments Calculation
        $stall = Stall::query()->where('id',(int) ($payload['stall_id']))
            ->get()->map(function ($data) {
                return [
                    'id' => $data->id,
                    'name' => $data->name,
                    'size' => $data->size,
                    'status' => $data->status,
                    'area_of_sqr_meter' => $data->area_of_sqr_meter,
                    'stall_category_id' => $data->stall_category_id,
                    'stallsCategories' => $data->stallsCategories ? [
                        'id' => $data->stallsCategories->id,
                        'name' => $data->stallsCategories->name,
                        'description' => $data->stallsCategories->description,
                        'is_transient' => $data->stallsCategories->is_transient,
                        'fee_masterlist_ids' => $data->stallsCategories->fee_masterlist_ids,
                        'fee' => FeeMasterlist::whereIn(
                            'id', // column name
                            json_decode($data->stallsCategories->fee_masterlist_ids, true) // array of IDs
                        )->get(),
                        'total_payment' => collect(json_decode(FeeMasterlist::whereIn('id', json_decode($data->stallsCategories->fee_masterlist_ids, true))->get(), true))    
                        ->sum(function ($f) {
                            return is_array($f) && isset($f['amount']) 
                                ? floatval($f['amount']) 
                                : 0.0;
                        }),
                    ] : null,
                ];
            })->first();
        $payload['assign_to'] = 10;
        $payload['department_id'] = 10;
        $permitDetails = $permitController->addBusinessPermit($payload);
    
        $payload['user_id'] = $user->id;
        $payload['permit_id'] = $permitDetails->id;

        $volanteRental = Volante::create($payload);
        $requirements = $requirementController->addRequirements(array_map(function ($item) use ($permitDetails) {
            $item['permit_id'] = $permitDetails->id;
            return $item;
        }, $payload['requirements']));
        
        $amountFee = $stall['stallsCategories']['fee']->toArray();
        $paymentDetails = $this->calculateVolantePaymentFromFees(
                $payload['started_date'],
                $payload['end_date'],
                $payload['duration'],
                isset($payload['quantity']) ? $payload['quantity'] : 0,
                $amountFee
        );
        Log::info('VolanteRental payload:', [
            'payload' => $payload,
            'paymentDetails' => $paymentDetails
        ]);
        $file = isset($payload['receipt']) ? $payload['receipt'] : null;
        $uuidName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads', $uuidName, 'public');
        $payments = $paymentController->addBusinessPermit([
            'user_id' => $volanteRental->user_id, // Assuming user ID is 2 for the example
            'volantes_id' => $volanteRental->id,
            'receipt' => $uuidName,
            'date' => Carbon::now()->format('Y-m-d'),
            'amount' => isset($payload['amount']) ? $payload['amount'] : 0,
            'reference_number' => isset($payload['reference_number']) ? $payload['reference_number'] : null,
            'status' => isset($payload['receipt']) ? 1 : 2, // 1 - Paid, 2 - Pending, 3 - Failed
        ]);

        $stallController->statusUpdate(
            new Request(['status' => 3]),
            Stall::findOrFail((int) $payload['stall_id'])
        );

        return [
            'volanteRental' => $volanteRental,
            'permitDetails' => $permitDetails,
            'requirements' => $requirements,
            'paymentDetails' => $paymentDetails,
            'payments' => $payments,
        ];
    }

    private function getCreatePayload(){
        return [
            'stallTypes' => Stall::whereIn('stall_category_id', [9, 10, 11])
                ->get()
                ->map(function ($data) {
                    return [
                        'id' => $data->id,
                        'name' => $data->name,
                        'location' => $data->location,
                        'categories' => $data->stallsCategories ? [
                            'id' => $data->stallsCategories->id,
                            'name' => $data->stallsCategories->name,
                            'description' => $data->stallsCategories->description,
                            'is_transient' => $data->stallsCategories->is_transient,
                            'fee_masterlist_ids' => $data->stallsCategories->fee_masterlist_ids,
                            'fee' => FeeMasterlist::whereIn(
                                'id', // column name
                                json_decode($data->stallsCategories->fee_masterlist_ids, true) // array of IDs
                            )->get(),
                            'total_payment' => collect(json_decode(FeeMasterlist::whereIn('id', json_decode($data->stallsCategories->fee_masterlist_ids, true))->get(), true))
                            ->sum(function ($f) {
                                return is_array($f) && isset($f['amount']) 
                                    ? floatval($f['amount']) 
                                    : 0.0;
                            }),
                        ] : null,
                        'area_of_sqr_meter' => $data->area_of_sqr_meter,
                        'size' => $data->size,
                        'status' => $data->status,
                    ];
                }),
            'requirements' => RequirementChecklist::query()->requirementsList()
            ];
    }

    private function updateData(Volante $volanteRental, array $payload)
    {
        Log::info('Updating Volante Rental: ', [
            'id' => $volanteRental->id,
            'payload' => $payload
        ]);

        $volanteRental->fill($payload)->save();

        // If stall_id is changed, update the stall status
        if (isset($payload['stall_id']) && $payload['stall_id'] != $volanteRental->stall_id) {
            $stallController = new StallsListController();
            $stallController->statusUpdate(
                new Request(['status' => 3]),
                Stall::findOrFail((int) $payload['stall_id'])
            );
        }
        Log::info('Volante Rental updated successfully.', [
            'id' => $volanteRental->id,
            'updated_data' => $volanteRental->toArray()
        ]);

        return $volanteRental;
    }

}
