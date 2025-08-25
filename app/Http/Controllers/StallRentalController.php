<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use App\Http\Requests\StallRentalRequest;

use App\Models\StallRental;
use App\Models\Stall;
use App\Models\RequirementChecklist;
use App\Models\StallsCategories;
use App\Models\FeeMasterlist;
use App\Models\User;

use App\Http\Controllers\PermitController;
use App\Http\Controllers\RequirementController;

use Carbon\Carbon;

class StallRentalController extends Controller
{
    public function index(Request $request): Response
    {   
        $user =  Auth::user();
        $data = [
            'filters' => $request->all('search', 'category'),
            'stallRentals' => $this->getIndexData($request),
            'stallsCategories' => StallsCategories::whereNotIn('id', [6, 7])->get(),
        ];
        return Inertia::render($user->role_id === 3 ? 'Users/Applications/StallRental'
        : 'Admin/Applications/StallRental', $data);
    }

    public function show(Request $request, StallRental $stallRental): RedirectResponse
    {
        $user = Auth::user();

        // Only allow access if user is role 2 and permit is assigned to their department
        if ($user->role_id === 1 || $user->role_id === 2 && $stallRental->permits && $stallRental->permits->department_id == $user->department_id) {
            return Inertia::render('Department/StallRental/Show', [
                'stallRental' => $this->getSingleData($stallRental),
            ]);
        }

        // Otherwise, redirect or abort
        return redirect()->route('department.applications.stalls')->with('error', 'Unauthorized access.');
    }

    public function edit(StallRental $stallRental): Response|RedirectResponse
    {
        $user = Auth::user();
        if ($user->role_id !== 3 && $stallRental->status === 1) {
            return redirect()->route('department.applications.stalls');
        }

        return Inertia::render('Users/Applications/StallRental/Form', array_merge(
                ['stallRental' => $this->getSingleData($stallRental)],
                $this->getCreatePayload()
        ));
    }

    public function store(StallRentalRequest $request)
    {
        $payload = $request->validated();
        // Step 1 → calculate payment
        if (isset($payload['step']) && $payload['step'] == 1) {
            $stall = Stall::with('stallsCategories')->findOrFail((int) $payload['stall_id']);
            $feeMasterlist = FeeMasterlist::whereIn(
                'id',
                json_decode($stall->stallsCategories->fee_masterlist_ids, true)
            )->get();

            $paymentDetails = $this->calculatestallRentalPaymentFromFees(
                $payload['started_date'],
                $payload['end_date'],
                $feeMasterlist->toArray()
            );

            // Merge into existing props (no redirect)
            return Inertia::render('Users/Applications/StallRental/Form', collect($this->getCreatePayload())
            ->merge(['paymentDetails' => $paymentDetails])
            ->toArray())->withViewData(['step' => 1]);
        }

        // Step 2 → just return current props, no changes
        if (isset($payload['step']) && $payload['step'] == 2) {
            return Inertia::render('Users/Applications/StallRental/Form')
                ->withViewData(['step' => 2]);
        }

        // Step 3 → final save
        if (isset($payload['step']) && $payload['step'] == 3) {
            $this->addData($payload);

            return redirect()->route('applications.stalls.index')->with('success', 'Stall rental application created successfully.');
        }

        // Default fallback
        return back()->withErrors(['step' => 'Invalid step provided.']);
    }

    public function create(): Response
    {
        return Inertia::render('Users/Applications/StallRental/Form', $this->getCreatePayload());
    }

    public function update(StallRentalRequest $request, StallRental $stallRental): Response|RedirectResponse
    {
        $this->updateData($stallRental, $request->all());
        return redirect()->route('applications.stalls.index')->with('success', 'Stall rental application updated successfully.');
    }

    public function approveRental(Request $request, StallRental $stallRental) : RedirectResponse
    {
        $rentalDetails = $this->getSingleData($stallRental);
        $approvalDetails = $this->approve($rentalDetails);
        return redirect()->route('department.applications.stalls')->with('success',  'Stall rental approved successfully.');
    }

    public function rejectRental(Request $request, StallRental $stallRental) : RedirectResponse
    {
        $request->validate([
            'remarks' => 'required|string|max:255',
        ]);
        $this->disApprove($stallRental, $request->all());
        return redirect()->route('department.applications.stalls')->with('success', 'Stall rental rejected.');
    }

    public function countAllPerCategoryAndStall() {
        return StallRental::with('stall.stallsCategories')
            ->where('status', 1)
            ->get()
            ->groupBy(fn ($rental) => $rental->stall?->stallsCategories?->name)
            ->map(fn ($group) => [
                'name' => $group->first()->stall->stallsCategories->name ?? 'Unknown',
                'total' => $group->count()
                ])->values();
    }

    public function countMonthlyApplicants(){
        $rawCounts = StallRental::selectRaw('EXTRACT(MONTH FROM created_at) AS month, COUNT(*) AS total')
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
        return StallRental::query()->totalTodayApplicants();
    }

    public function indexApi(Request $request)
    {
        // $user = Auth::user();
        $stallRentals = $this->getIndexData($request);

        return response()->json([
            'filters' => $request->all('search', 'stallType'),
            'stallRentals' => $stallRentals,
            'stallsCategories' => StallsCategories::all(),
        ]);

    }

    public function showApi(Request $request, StallRental $stallRental)
    {
        return response()->json([
            'stallRental' => $this->getSingleData($stallRental),
        ]);
    }

    public function storeApi(StallRentalRequest $request)
    {  
        $stallRental = $this->addData($request->validated());
        return response()->json(['data' => $stallRental ]);
    }

    public function createApi()
    {
        return response()->json($this->getCreatePayload());

    }

    public function editApi(StallRental $stallRental)
    {
      return response()->json(
            array_merge(
                ['stallRental' => $this->getSingleData($stallRental)],
                $this->getCreatePayload()
            )
        );

    }

    public function updateApi(StallRentalRequest $request, StallRental $stallRental)
    {
        return response()->json(
            array_merge(
                ['stallRental' => $this->updateData($stallRental, $request->all())],
            )
        );
    }

    public function approveOrReject(Request $request, StallRental $stallRental)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $statusValue = $request->status === 'approved' ? 1 : 2; // 1 = approved, 2 = rejected (adjust as needed)

        $stallRental->status = $statusValue;
        $stallRental->save();

        return response()->json([
            'message' => 'Stall rental status updated successfully.',
            'status' => $stallRental->status,
        ]);
    }

    private function calculatestallRentalPaymentFromFees(
    string $startDate,
    string $endDate,
    array $fees
    ) {
        $days = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $months = ceil($days / 30);
        $total = 0;
        $feeBreakdown = [];

        foreach ($fees as $fee) {
            $amount = (float) $fee['amount'];
            $feeAmount = 0;

            if (!empty($fee['is_daily'])) {
                $feeAmount = $amount * $days;
            } elseif (!empty($fee['is_monthly'])) {
                $feeAmount = $amount * $months;
            } else {
                // One-time fee
                $feeAmount = $amount;
            }

            $feeBreakdown[] = [
                'type'   => $fee['type'],
                'amount' => $feeAmount,
            ];

            $total += $feeAmount;
        }

        return [
            'days'       => $days,
            'months'     => $months,
            'breakdown'  => $feeBreakdown,
            'total'      => $total,
        ];
    }



    private function getIndexData(Request $request) {
        $user = Auth::user();

        $query = StallRental::query()->filter($request->all('search', 'category'));

        if ($user && $user->role_id === 3) {
            $query->myApplication($user->id);
        }

        if ($user && $user->role_id === 2) {
            $query->myApplicationUnderMyDep($user->department_id);
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

                $paymentDetails = $this->calculatestallRentalPaymentFromFees(
                    $data->started_date,
                    $data->end_date,
                    $feeMasterlist->toArray()
                );

                return [
                    'id' => $data->id,
                    'name' => $data->business_name,
                    'status' => $data->status,
                    'area_of_sqr_meter' => $data->area_of_sqr_meter,
                    'start_date' => Carbon::parse($data->started_date),
                    'end_date' => Carbon::parse($data->end_date),
                    'stalls' => $stall ? [
                        'id' => $stall->id,
                        'name' => $stall->name,
                        'size' => $stall->size,
                        'status' => $stall->status,
                        'location' => $stall->location,
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
                            'attachment' => $requirement->attachment,
                            'name' => $requirement->requirementDetails->name,
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

    private function getCreatePayload(){
        return [
            'stallTypes' => Stall::whereNotIn('stall_category_id', [6, 7])
                ->get()
                ->map(function ($data) {
                    return [
                        'id' => $data->id,
                        'name' => $data->name,
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

    private function getSingleData(StallRental $stallRental) {
        $user = Auth::user();

        $stall = $stallRental->stalls;
        Log::info('Fetching single stall rental data', [
            'stall_rental_id' => $stallRental->user_id,
            'user_id' => $user ? $user->id : null,
            'role_id' => $user ? $user->role_id : null,
        ]);
        if ($user->role_id === 3 && $stallRental->user_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        } elseif ($user->role_id === 2 && $stall && $stallRental->permits && $stallRental->permits->department_id !== $user->department_id) {
            abort(403, 'Unauthorized access.');
        }
        // role_id === 1 (admin) can access all, so no restriction

        $stallCategory = $stall ? $stall->stallsCategories : null;
        $permits = $stallRental->permits;
        $feeMasterlist = $stallCategory ? FeeMasterlist::whereIn(
            'id',
            json_decode($stallCategory->fee_masterlist_ids, true)
        )->get() : collect();
        return [
            'id' => $stallRental->id,
            'name' => $stallRental->business_name,
            'status' => $stallRental->status,
            'area_of_sqr_meter' => $stallRental->area_of_sqr_meter,
            'start_date' => Carbon::parse($stallRental->started_date),
            'end_date' => Carbon::parse($stallRental->end_date),
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
            'payments' => $this->calculatestallRentalPaymentFromFees(
                $stallRental->started_date,
                $stallRental->end_date,
                $feeMasterlist->toArray()
            ),
            'paymentRecord' => $stallRental->payments,
            'user' => $stallRental->user,
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
        
        $permitDetails = $permitController->addBusinessPermit($payload);
            

        $payload['user_id'] = $user->id;
        $payload['permit_id'] = $permitDetails->id;
       
        $stallRental = StallRental::create($payload);
        $requirements = $requirementController->addRequirements(array_map(function ($item) use ($permitDetails) {
            $item['permit_id'] = $permitDetails->id;
            return $item;
        }, $payload['requirements']));

        
        $amountFee = $stall['stallsCategories']['fee']->toArray();
        $paymentDetails = $this->calculatestallRentalPaymentFromFees(
                $payload['started_date'],
                $payload['end_date'],
                $amountFee
        );
        Log::info('STallRental payload:', [
            'payload' => $payload,
            'requirements' => $requirements,
            'paymentDetails' => $paymentDetails
        ]);

        $file = isset($payload['receipt']) ? $payload['receipt'] : null;
        $uuidName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads', $uuidName, 'public');
        $payments = $paymentController->addBusinessPermit([
            'user_id' => $stallRental->user_id, // Assuming user ID is 2 for the example
            'stall_rental_id' => $stallRental->id,
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
            'stallRental' => $stallRental,
            'permitDetails' => $permitDetails,
            'requirements' => $requirements,
            'paymentDetails' => $paymentDetails,
            'payments' => $payments,
        ];
    }

    private function updateData(StallRental $stallRental, array $payload)
    {
        $stallRental->fill($payload)->save();

        // If stall_id is changed, update the stall status
        if (isset($payload['stall_id']) && $payload['stall_id'] != $stallRental->stall_id) {
            $stallController = new StallsListController();
            $stallController->statusUpdate(
                new Request(['status' => 3]),
                Stall::findOrFail((int) $payload['stall_id'])
            );
        }

        // Update requirements if present in payload
        if (isset($payload['requirements']) && is_array($payload['requirements'])) {
            $requirementController = new RequirementController();
            foreach ($payload['requirements'] as $requirementData) {
                if (isset($requirementData['id'])) {
                    // Update existing requirement
                    $requirementController->updateRequirement($requirementData['id'], $requirementData);
                } else {
                    // Add new requirement (attach to permit if possible)
                    $requirementData['permit_id'] = $stallRental->permit_id;
                    $requirementController->addRequirements([$requirementData]);
                }
            }
        }

        Log::info('stall Rental updated successfully.', [
            'id' => $stallRental->id,
            'updated_data' => $stallRental->toArray()
        ]);

        return $stallRental;
    }

      private function disApprove($stallRental, array $payload) {
        $this->updateData($stallRental, ['status' => 3]);
        $PermitController = new PermitController();
        $PermitController->update([
                'status' => 2, //rejected
                'remarks' => $payload['remarks'],
                'assign_to' => 1, // 1 = user
                'is_initial' => false, // set to false when forwarded to user
            ], $stallRental->permit_id);
    }

    private function approve($stallRental) {
        $user = Auth::user();
        $permit = (object)$stallRental['permits'];

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

            $this->updateData($stallRental, ['status' => 1]);

            $stallController = new StallsListController();
            // Update stall status to 'assigned' (3)
            $stallController->statusUpdate(
                new Request(['status' => 1]),
                Stall::findOrFail((int) $stallRental->stall_id)
            );
        }
        return [
            'message' => 'Volante rental approved successfully.',
            'stallRental' => $stallRental,
            'permit' => $permit
        ];

    }
}
