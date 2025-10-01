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
use App\Http\Controllers\PaymentController;
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
            'stallsCategories' => StallsCategories::whereIn('id', [4, 5, 6])->get(),
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

    public function store(VolanteRentalRequest $request)
    {
        $payload = $request->validated();
        $total_payments = 0;
        // Step 1 → calculate payment
        if (isset($payload['step']) && $payload['step'] == 1) {
            return Inertia::render('Users/Applications/Volante/Form', collect($this->getCreatePayload()
            ->merge(['paymentDetails' => $paymentDetails])
            ->toArray())->withViewData(['step' => 1]));
        }

        // Step 2 → just return current props, no changes
        if (isset($payload['step']) && $payload['step'] == 2) {
            return Inertia::render('Users/Applications/Volante/Form')
                ->withViewData(['step' => 2]);
        }

        // Step 3 → final save
        if (isset($payload['step']) && $payload['step'] == 3) {
            $stall = Stall::with('stallsCategories')->findOrFail((int) $payload['stall_id']);
            list($fees_additional) = $this->prepareFees($request, $stall);

            $total_payments = $this->calculateVolantePaymentFromFees(
                (int) $request->bulb,
                $stall,
                $fees_additional
            );


            $payloadRequest = $payload;
            $payloadRequest['total_payment'] = $total_payments->total;
            $payloadRequest['fees_additional'] = json_encode($request->fees ?? []);


            Log::info('Final payload for stall rental creation', [
                'payload' => $payloadRequest,
            ]);

            $this->addData($payload);

            return redirect()
                ->route('applications.volantes.index')
                ->with('success', 'Volante rental application created successfully.');
        }

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
        $payload = $request->validated();
        $total_payments = 0;
        // Step 1 → calculate payment
        if (isset($payload['step']) && $payload['step'] == 1) {
            $stall = Stall::with('stallsCategories')->findOrFail((int) $payload['stall_id']);
            list($fees_additional) = $this->prepareFees($request, $stall);
            $total_payments = $this->calculateVolantePaymentFromFees(
                (int) $request->bulb,
                $stall,
                $fees_additional
            );
            return response()->json([
                'step' => 1,
                'data' => $this->getCreatePayload(),
                'stall' => $stall,
                'total_payments' => $total_payments->total,
            ]);
        }

        // Step 2 → just return current props, no changes
        if (isset($payload['step']) && $payload['step'] == 2) {
            return response()->json([
                'step' => 2,
            ]);
        }

        // Step 3 → final save
        if (isset($payload['step']) && $payload['step'] == 3) {
            $stall = Stall::with('stallsCategories')->findOrFail((int) $payload['stall_id']);
            list($fees_additional) = $this->prepareFees($request, $stall);

            $total_payments = $this->calculateVolantePaymentFromFees(
                (int) $request->bulb,
                $stall,
                $fees_additional
            );


            $payloadRequest = $payload;
            $payloadRequest['total_payment'] = $total_payments->total;
            $payloadRequest['fees_additional'] = json_encode($request->fees ?? []);

            // $payloadRequest = (object) $payloadRequest;

            Log::info('Final payload for stall rental creation', [
                'payload' => $payloadRequest,
            ]);

            $this->addData($payloadRequest);

            return response()->json([
                'step'    => 3,
                'message' => 'Stall rental application created successfully.',
                'data'    => $payloadRequest,
            ]);
        }


        return response()->json([
            'step' => null,
            'message' => 'Invalid step provided.',
        ], 400);
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
        $approvalPermitController = new ApprovalPermitController();
        $approvalPermitController->storeApproval(
            $permit->id, 
            $currentDeptId,
            $currentDeptId,
            $user->id
        );
        $permitController = new PermitController();

        // STEP 1: Initial → SMPO
        if ($permit->is_initial) {
             $permitController->update([
                'department_id' => 3, // Office of the Mayor
                'assign_to' => 2, // assign to approver
                'is_initial' => false
            ], $permit->id);
           
            return response()->json(['message' => 'Office of Public Market Initial approval done. Sent to Office of the Mayor.']);
        }

        // STEP 3: Office of the Mayor >   Market → Final Approval
        if (!$permit->is_initial && $currentDeptId === 3 && $permit->status == 0) {
             $permitController->update([
                'department_id' => 2, // Office of the Public Market
            ], $permit->id);
            
            $requiredDepartments = [
                2, // Public Market
                3, // OFFICE OF THE MUNICIPAL MAYOR
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
                'issued_date'   => Carbon::now()->format('Y-m-d'),
                'expiry_date'   => Carbon::now()->copy()->addYears(3)->toDateString(),
                'assign_to'     => 1,
                'permit_number' => $permit->generatePermitNumber(10),
                'department_id' => null
            ], $permit->id);

            $this->updateData($volanteRental, ['status' => 1]);

            $stallController = new StallsListController();
            // Update stall status to 'assigned' (1)
            $stallController->statusUpdate(
                new Request(['status' => 1]),
                Stall::findOrFail((int) $volanteRental->stall_id)
            );
        }
        // Send SMS or Email notification to user about approval
        // Notification::send($stallRental->user, new StallRentalApproved($stallRental));
        return [
            'message' => 'Volante rental approved successfully.',
            'volanteRental' => $volanteRental,
            'permit' => $permit
        ];

    }

    private function prepareFees($request, $stall): array
    {

        // Merge request fees with bulb fee if needed
        $feesIds = $request->fees_additional ? json_decode($request->fees_additional , true) : $request->fees ?? [];
        if (!is_array($feesIds)) {
            $feesIds = json_decode($feesIds, true) ?? [];
        }

        if ((int) $request->bulb > 0) {
            $feesIds = array_merge($feesIds, [3]); // id 3 is Bulbs
        }

        // Fetch all fees from DB
        $fees = FeeMasterlist::whereIn('id', $feesIds)->get()->map(function ($f) {
            return [
                'id'     => $f->id,
                'type'   => $f->type,
                'amount' => $f->amount,
            ];
        })->toArray();

        return [ $fees];
    }

    private function calculateVolantePaymentFromFees(
        int $bulb,
        Stall $stall,
        array $fees,
        ?bool $isNotPaid = null, // make it optional
    ) {
        $total = 0;
        $breakdown = [];

        // Ensure fees is always an array
        if (is_string($fees)) {
            $fees = json_decode($fees, true);
        }

        // Add fixed fees except bulbs
        foreach ($fees as $fee) {
            $type = $fee['type'];
            $amount = $fee['amount'];

            if ($type !== 'Bulbs') {
                $breakdown[] = (object) ['type' => $type, 'amount' => $amount];
                $total += $amount;
            }
        }

        // Add bulb charges
        
        foreach ($fees as $fee) {
            $type = $fee['type'];
            $amount = $fee['amount'];

            if ($type === 'Bulbs' && $bulb > 0) {
                $bulbFee = $bulb * $amount;
                $breakdown[] = (object) ['type' => "Bulbs ({$bulb} pcs)", 'amount' => $bulbFee];
                $total += $bulbFee;
            }
        }

        // Apply surcharge if unpaid
        if ($isNotPaid) {
            $surcharge = $total * 0.12;
            $breakdown[] = (object) ['type' => '12% Surcharge (Unpaid)', 'amount' => $surcharge];
            $total += $surcharge;
        }

        return (object) [
            'stall' => $stall,
            'breakdown' => $breakdown,
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
            $query->myApplicationUnderMyDep($user->department_id);
        }

        return $query->paginate(10)
            ->through(function ($volanteRental) use ($user) {
                $stall = $volanteRental->stalls;
                $stallCategory = $stall ? $stall->stallsCategories : null;
                $permits = $volanteRental->permits;

                // penalty check
                $startDate = $volanteRental->started_date; 

                $isCurrentQuarterPaid = false;

                if ($startDate && !$startDate->isSameMonth(now())) {
                    $isCurrentQuarterPaid = $volanteRental->payments()
                        ->inCurrentQuarter()
                        ->where('volantes_id', $volanteRental->id)
                        ->exists();
                }
                        
                // fees
                list($fees_additional) = $this->prepareFees($volanteRental, $stall);

                $total_payments = $this->calculateVolantePaymentFromFees(
                    (int) $volanteRental->bulb,
                    $stall,
                    $fees_additional
                );

               $currentPayment = $volanteRental->payments()
                    ->withVolanteRental()
                    ->thisMonth()
                    ->get();

                return [
                    'vendor' => $volanteRental->user,
                    'id' => $volanteRental->id,
                    'name' => $volanteRental->business_name,
                    'status' => $volanteRental->status,
                    'start_date' => $volanteRental->started_date ? Carbon::parse($volanteRental->started_date) : null,
                    'end_date' => $volanteRental->started_date ? Carbon::parse($volanteRental->end_date) : null,
                    'acknowledgeContract' => $volanteRental->acknowledgeContract,
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
                            'fee_masterlist_ids' => $stallCategory->fee_masterlist_id,
                            'fee' => FeeMasterlist::where(            
                                'id',
                            $stallCategory->fee_masterlist_id
                            )->get(),
                        ] : null,
                    ] : null,
                    'permits' => $permits ? [
                        'id' => $permits->id,
                        'type' => $permits->type === 1 ? 'New' : 'Renewal',
                        'permit_number' => $permits->permit_number,
                        'department_id' => $permits->department_id,
                        'status' => $permits->status,
                        'remarks' => $permits->remarks,
                        'assign_to' => $permits->assign_to,
                        'is_initial' => $permits->is_initial,
                        'created_at' => $permits->created_at ? Carbon::parse($permits->created_at) : null,
                        'issued_date' => $permits->issued_date ? Carbon::parse($permits->issued_date) : null,
                        'expiry_date' => $permits->expiry_date ? Carbon::parse($permits->expiry_date) : null
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
                    'quarterly_payment' => $total_payments->total,
                    'next_payment_due' => $this->nextStartDate(),
                    'penalty' => $isCurrentQuarterPaid ? ($total_payments->total * 0.12) : 0,
                    'currentPayment' => $currentPayment,
                    'current_payment' => count($currentPayment) > 0 ? 'Paid' : 'Not Paid',
                    'approvals' => $permits->approvals ? $permits->approvals->map(function ($approval) {
                        return [
                            'id' => $approval->id,
                            'approver' => $approval->approver ? [
                                'id' => $approval->approver->id,
                                'name' => $approval->approver->first_name . ' ' . $approval->approver->last_name,
                                'email' => $approval->approver->email,
                            ] : null,
                            'department' => $approval->department->name,
                            'status' => $approval->status,
                            'created_at' => $approval->created_at ? Carbon::parse($approval->created_at) : null,
                        ];
                    }) : collect(),
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

        // penalty check
        $startDate = $volanteRental->started_date; 

        $isCurrentQuarterPaid = false;

        if ($startDate && !$startDate->isSameMonth(now())) {
            $isCurrentQuarterPaid = $volanteRental->payments()
                ->inCurrentQuarter()
                ->where('volantes_id', $volanteRental->id)
                ->exists();
        }

        // fees
        list($fees_additional) = $this->prepareFees($volanteRental, $stall);

        $total_payments = $this->calculateVolantePaymentFromFees(
            (int) $volanteRental->bulb,
            $stall,
            $fees_additional,
            $isCurrentQuarterPaid
        );

        $currentPayment = $volanteRental->payments()
            ->withVolanteRental()
            ->thisMonth()
            ->get();
        return [
            'vendor' => $volanteRental->user,
            'id' => $volanteRental->id,
            'name' => $volanteRental->business_name,
            'location' => $volanteRental->location,
            'duration' => $volanteRental->duration,
            'quantity' => $volanteRental->quantity,
            'status' => $volanteRental->status,
            'area_of_sqr_meter' => $volanteRental->area_of_sqr_meter,
            'start_date' => $volanteRental->started_date ? Carbon::parse($volanteRental->started_date) : null,
            'end_date' => $volanteRental->end_date ? Carbon::parse($volanteRental->end_date) : null,
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
                    'fee_masterlist_ids' => $stallCategory->fee_masterlist_id,
                    'fee' => FeeMasterlist::where(            
                        'id',
                       $stallCategory->fee_masterlist_id
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
                'expiry_date' => $permits->expiry_date ? Carbon::parse($permits->expiry_date) : null
            ] : null,
            'requirements' => $permits ? $permits->requirements->map(function ($requirement) {
                return [
                    'id' => $requirement->id,
                    'attachment' => $requirement->attachment,   
                    'checklist_id' => $requirement->requirement_checklist_id,
                    'url' => asset('storage/uploads' . $requirement->path),
                ];
            }) : collect(),
            'quarterly_payment' => $total_payments->total,
            'next_payment_due' => $this->nextStartDate(),
            'penalty' => $isCurrentQuarterPaid ? ($total_payments->total * 0.12) : 0,
            'currentPayment' => $currentPayment,
            'current_payment' => count($currentPayment) > 0 ? 'Paid' : 'Not Paid',
            'approvals' => $permits->approvals ? $permits->approvals->map(function ($approval) {
                return [
                    'id' => $approval->id,
                    'approver' => $approval->approver ? [
                        'id' => $approval->approver->id,
                        'name' => $approval->approver->first_name . ' ' . $approval->approver->last_name,
                        'email' => $approval->approver->email,
                        'position' => $approval->approver->departmentPositions ? $approval->approver->departmentPositions->name : null
                    ] : null,
                    'department' => $approval->department->name,
                    'status' => $approval->status,
                    'created_at' => $approval->created_at ? Carbon::parse($approval->created_at) : null,
                ];
            }) : collect(),
        ];  
    }

    private function nextStartDate() {
        $nextMonth20 = Carbon::now()->addMonth()->day(20);

        return $nextMonth20->toDateString();
    }

    private function addData(array $payload)
    {
        $user = User::find(5); //Auth::user();
        $requirementController = new RequirementController();
        $paymentController = new PaymentController();
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
                    'coordinates' => $data->coordinates,
                    'stall_category_id' => $data->stall_category_id,
                    'stallsCategories' => $data->stallsCategories,
                ];
            })->first();
        $payload['assign_to'] = 2;
        $payload['department_id'] = 10;
        $permitDetails = $permitController->addBusinessPermit($payload);
            
        $file = isset($payload['attachment_signature']) ? $payload['attachment_signature'] : null;
        $uuidName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads', $uuidName . 'attachment_signature' . $payload['business_name'], 'public');
        $payload['user_id'] = $user->id;
        $payload['permit_id'] = $permitDetails->id;
        $payload['attachment_signature'] = 'attachment_signature-'.$payload['business_name'].'-'.$uuidName;

        $volanteRental = Volante::create($payload);
        $requirements = $requirementController->addRequirements(array_map(function ($item) use ($permitDetails) {
            $item['permit_id'] = $permitDetails->id;
            return $item;
        }, $payload['requirements']));

        
        $amountFee = $payload['total_payment'] ?? 0;

        // Update stall to Reserved
        $stallController->statusUpdate(
            new Request(['status' => 3]),
            Stall::findOrFail((int) $payload['stall_id'])
        );

        return [
            'volanteRental' => $volanteRental,
            'permitDetails' => $permitDetails,
            'requirements' => $requirements,
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
