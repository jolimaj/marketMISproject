<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

use App\Http\Requests\TablesRequest;
use App\Http\Requests\PaymentsRequest;

use App\Models\TableRental;
use App\Models\Stall;
use App\Models\RequirementChecklist;
use App\Models\StallsCategories;
use App\Models\FeeMasterlist;
use App\Models\User;

use App\Http\Controllers\PermitController;
use App\Http\Controllers\RequirementController;

use Carbon\Carbon;
use Illuminate\Support\Str;

class TablesController extends Controller
{
    public function index(Request $request): Response
    {
        $user =  Auth::user();
        $data = [
            'filters' => $request->all('search', 'category'),
            'tableRentals' => $this->getIndexData($request),
            'stallsCategories' => StallsCategories::query()->where('is_table_rental', true)->get(),
        ];
        return Inertia::render($user->role_id === 3 ? 'Users/Applications/TableRental'
            : 'Admin/Applications/TableRental', $data);
    }

    public function create(): Response
    {
        // Get necessary data for creating a table rental
        $createPayload = $this->getCreatePayload();
        return Inertia::render('Users/Applications/TableRental/Create', $createPayload);
    }

    public function store(TablesRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $total_payments = 0;
        // Step 1 → calculate payment
        if (isset($payload['step']) && $payload['step'] == 1) {
            return Inertia::render('Users/Applications/TableRental/Form', 
            $this->getCreatePayload()->withViewData(['step' => 1]));
        }

        // Step 2 → just return current props, no changes
        if (isset($payload['step']) && $payload['step'] == 2) {
            return Inertia::render('Users/Applications/TableRental/Form')
                ->withViewData(['step' => 2]);
        }

        // Step 3 → final save
        if (isset($payload['step']) && $payload['step'] == 3) {
            $stall = Stall::with('stallsCategories')->findOrFail((int) $payload['stall_id']);
            list($feeMasterlist, $fees_additional) = $this->prepareFees($request, $stall);

            $total_payments = $this->calculatetableRentalPaymentFromFees(
                (int) $request->bulb,
                $stall,
                $feeMasterlist,
                $fees_additional
            );


            $payloadRequest = $payload;
            $payloadRequest['total_payment'] = $total_payments->total;
            $payloadRequest['fees_additional'] = json_encode($request->fees ?? []);

            $this->addData($payloadRequest);

            return redirect()->route('applications.table.index')->with('success', 'Table rental application created successfully.');
        }


        return back()->withErrors(['step' => 'Invalid step provided.']);

    }

    public function show(string $id): Response
    {
        $user = Auth::user();

        // Only allow access if user is role 2 and permit is assigned to their department
        if ($user->role_id === 1 || $user->role_id === 2 && $tableRental->permits && $tableRental->permits->department_id == $user->department_id) {
            return Inertia::render('Department/TableRental/Show', [
                'tableRental' => $this->getSingleData($tableRental),
            ]);
        }

        // Otherwise, redirect or abort
        return redirect()->route('department.applications.table')->with('error', 'Unauthorized access.');
    }

    public function edit(TableRental $tableRental): Response|RedirectResponse
    {
        $user = Auth::user();
        if ($user->role_id !== 3 && $tableRental->status === 1) {
            return redirect()->route('department.applications.table');
        }

        return Inertia::render('Users/Applications/TableRental/Form', array_merge(
                ['tableRental' => $this->getSingleData($tableRental)],
                $this->getCreatePayload()
        ));
    }

    public function update(TablesRequest $request, TableRental $tableRental): Response|RedirectResponse
    {
        $this->updateData($tableRental, $request->all());
        return redirect()->route('applications.table.index')->with('success', 'Table rental application updated successfully.');
    }

    public function approveTable(Request $request, TableRental $tableRental) : RedirectResponse
    {
        $rentalDetails = $this->getSingleData($tableRental);
        $approvalDetails = $this->approve($rentalDetails);
        return redirect()->back()->with('success', $approvalDetails['message'] ?? 'Table rental approved successfully.');
    }

    public function rejectTable(Request $request, TableRental $tableRental) : RedirectResponse
    {
        $request->validate([
            'remarks' => 'required|string|max:255',
        ]);
        $this->disApprove($tableRental, $request->all());
        return redirect()->route('department.applications.table')->with('success', 'Table rental rejected.');
    }

    // api
    public function indexApi(Request $request)
    {
        $tableRentals = $this->getIndexData($request);

        return response()->json([
            'filters' => $request->all('search', 'stallType'),
            'tableRentals' => $tableRentals,
            'stallsCategories' => StallsCategories::query()->where('is_table_rental', true)->get(),
        ]);

    }

    public function storeApi(TablesRequest $request)
    {  
        $payload = $request->validated();
        $total_payments = 0;
        // Step 1 → calculate payment
        if (isset($payload['step']) && $payload['step'] == 1) {
            $stall = Stall::with('stallsCategories')->findOrFail((int) $payload['stall_id']);
            list($feeMasterlist, $fees_additional) = $this->prepareFees($request, $stall);
            
            $total_payments = $this->calculatetableRentalPaymentFromFees(
                (int) $request->bulb,
                $stall,
                $feeMasterlist,
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
            list($feeMasterlist, $fees_additional) = $this->prepareFees($request, $stall);

            $total_payments = $this->calculatetableRentalPaymentFromFees(
                (int) $request->bulb,
                $stall,
                $feeMasterlist,
                $fees_additional
            );


            $payloadRequest = $payload;
            $payloadRequest['total_payment'] = $total_payments->total;
            $payloadRequest['fees_additional'] = json_encode($request->fees ?? []);

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
    
    public function showApi(TableRental $tableRental)
    {
        $tableRentalDetails = $this->getSingleData($tableRental);

        return response()->json([
            'tableRental' => $tableRentalDetails,
        ]);
    }

    public function createApi()
    {
        return response()->json($this->getCreatePayload());

    }

    public function editApi(TableRental $tableRental)
    {
        return response()->json(array_merge(
            ['tableRental' => $this->getSingleData($tableRental)],
            $this->getCreatePayload()
        ));
    }

    public function updateApi(TablesRequest $request, TableRental $tableRental)
    {
        $this->updateData($tableRental, $request->all());

        return response()->json([
            'message' => 'Table rental application updated successfully.',
            'tableRental' => $this->getSingleData($tableRental),
        ]);
    }

    public function countTodayApplicants(){
        return TableRental::query()->totalTodayApplicants();
    }

    // private functions
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
        
        $permitDetails = $permitController->addBusinessPermit($payload);
            
        $file = isset($payload['attachment_signature']) ? $payload['attachment_signature'] : null;
        $uuidName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads', $uuidName . 'attachment_signature' . $payload['business_name'], 'public');
        $payload['user_id'] = $user->id;
        $payload['permit_id'] = $permitDetails->id;
        $payload['attachment_signature'] = 'attachment_signature-'.$payload['business_name'].'-'.$uuidName;

        $tableRental = TableRental::create($payload);
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
            'tableRental' => $tableRental,
            'permitDetails' => $permitDetails,
            'requirements' => $requirements,
        ];
    }

    private function getCreatePayload(){
        return [
            'stallTypes' => Stall::whereIn('stall_category_id', [1,2,3])
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
                            'fee_masterlist_ids' => $data->stallsCategories->fee_masterlist_id,
                            'fee' => FeeMasterlist::where(
                                'id', // column name
                                $data->stallsCategories->fee_masterlist_id // array of IDs
                            )->get(),
                        ] : null,
                        'coordinates' => $data->stall->coordinates ?? null,
                        'size' => $data->stall->size ?? null,
                        'status' => $data->status,
                    ];
                }),
            'requirements' => RequirementChecklist::query()->requirementsList(),
        ];
    }

    private function getIndexData(Request $request) {
        $user = Auth::user();
        
        $query = TableRental::query()->filter($request->all('search', 'category'));

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

                // penalty check
                $startDate = $data->started_date; 

                $isCurrentQuarterPaid = false;

                if ($startDate && !$startDate->isSameMonth(now())) {
                    $isCurrentQuarterPaid = $data->payments()
                        ->inCurrentQuarter()
                        ->where('table_rental_id', $data->id)
                        ->exists();
                }

                // fees
                list($feeMasterlist, $fees_additional) = $this->prepareFees($data, $stall);
                $paymentDetails = $this->calculateTableRentalPaymentFromFees(
                (int) $data->bulb,
                    $stall,
                    $feeMasterlist,
                    $fees_additional,
                    $isCurrentQuarterPaid
                );

                $currentPayment = $data->payments()
                    ->withTableRental()
                    ->thisMonth()
                    ->get();

                return [
                    'vendor' => $data->user,
                    'id' => $data->id,
                    'name' => $data->business_name,
                    'status' => $data->status,
                    'start_date' => $data->started_date ? Carbon::parse($data->started_date) : null,
                    'end_date' => $data->started_date ? Carbon::parse($data->end_date) : null,
                    'acknowledgeContract' => $data->acknowledgeContract,
                    'stalls' => $stall ? [
                        'id' => $stall->id,
                        'name' => $stall->name,
                        'size' => $stall->size,
                        'status' => $stall->status,
                        'location' => $stall->location,
                        'coordinates' => $stall->coordinates,
                        'stall_category_id' => $stall->stall_category_id,
                        'stallsCategories' => $stallCategory ? [
                            'id' => $stallCategory->id,
                            'name' => $stallCategory->name,
                            'description' => $stallCategory->description,
                            'is_transient' => $stallCategory->is_transient,
                            'fee_masterlist_ids' => $stallCategory->fee_masterlist_ids,
                            'fee' => FeeMasterlist::where(
                                'id',
                               $stallCategory->fee_masterlist_id
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
                        'expiry_date' => $permits->expiry_date ? Carbon::parse($permits->expiry_date) : null,
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
                    'paymentDetails' => $paymentDetails,
                    'quarterly_payment' => $paymentDetails->total,
                    'next_payment_due' => $this->quarterStartDate(),
                    'penalty' => $isCurrentQuarterPaid ? ($paymentDetails->total * 0.12) : 0,
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

    private function prepareFees($request, $stall): array
    {
        // Base fee from stall category
        $feeMasterlist = FeeMasterlist::where(
            'id',
            json_decode($stall->stallsCategories->fee_masterlist_id, true)
        )->first();

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

        return [$feeMasterlist, $fees];
    }

    private function getSingleData(TableRental $tableRental) {
        $user = Auth::user();
        $stall = $tableRental->stalls;
        Log::info('Fetching single table rental data', [
            'stall_rental_id' => $tableRental->user_id,
            'user_id' => $user ? $user->id : null,
            'role_id' => $user ? $user->role_id : null,
        ]);
        if ($user->role_id === 3 && $tableRental->user_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        } elseif ($user->role_id === 2 && $stall && $tableRental->permits && $tableRental->permits->department_id !== $user->department_id) {
            abort(403, 'Unauthorized access.');
        }

        $stallCategory = $stall ? $stall->stallsCategories : null;
        $permits = $tableRental->permits;

        // penalty check
        $startDate = $tableRental->started_date; 

        $isCurrentQuarterPaid = false;

        if ($startDate && !$startDate->isSameMonth(now())) {
            $isCurrentQuarterPaid = $tableRental->payments()
                ->inCurrentQuarter()
                ->where('table_rental_id', $tableRental->id)
                ->exists();
        }

        // fees
        list($feeMasterlist, $fees_additional) = $this->prepareFees($tableRental, $stall);
        $paymentDetails = $this->calculatetableRentalPaymentFromFees(
            (int) $tableRental->bulb,
                $stall,
                $feeMasterlist,
                $fees_additional,
                $isCurrentQuarterPaid
            );

        $currentPayment = $data->payments()
            ->withStallRental()
            ->thisMonth()
            ->get();
        return [
            'vendor' => $tableRental->user,
            'id' => $tableRental->id,
            'name' => $tableRental->business_name,
            'status' => $tableRental->status,
            'coordinates' => $tableRental->coordinates,
            'start_date' => $tableRental->started_date ? Carbon::parse($tableRental->started_date) : null,
            'end_date' => $tableRental->started_date ? Carbon::parse($tableRental->end_date) : null,
            'stalls' => $stall ? [
                'id' => $stall->id,
                'name' => $stall->name,
                'size' => $stall->size,
                'status' => $stall->status,
                'coordinates' => $stall->coordinates,
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
                'expiry_date' => $permits->expiry_date ? Carbon::parse($permits->expiry_date) : null,
            ] : null,
            'requirements' => $permits ? $permits->requirements->map(function ($requirement) {
                return [
                    'id' => $requirement->id,
                    'attachment' => $requirement->attachment,   
                    'checklist_id' => $requirement->requirement_checklist_id,
                    'url' => asset('storage/uploads' . $requirement->path),
                ];
            }) : collect(),
            'paymentDetails' => $paymentDetails,
            'quarterly_payment' => $paymentDetails->total,
            'next_payment_due' => $this->quarterStartDate($tableRental->payments()
                ->withTableRental()
                ->thisMonth()
                ->get()),
            'penalty' => $isCurrentQuarterPaid ? ($paymentDetails->total * 0.12) : 0,
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
   
    private function quarterStartDate() {
        $today = Carbon::now();

        // start of current quarter
        $quarterStart = $today->firstOfQuarter();

        // move to next quarter
        $nextQuarterStart = $quarterStart->copy()->addQuarter();

        // set to 20th day
        $nextQuarter20th = $nextQuarterStart->day(20);

        return $nextQuarter20th->toDateString();
    }

    private function calculateTableRentalPaymentFromFees(
        int $bulb,
        Stall $stall,
        FeeMasterlist $feeMasterlist,
        array|string $fees,
        ?bool $isNotPaid = null, // make it optional
    ) {
        $total = 0;
        $breakdown = [];

        // Ensure fees is always an array
        if (is_string($fees)) {
            $fees = json_decode($fees, true);
        }

        Log::info('Calculating table rental payment from fees', [
            'bulb' => $bulb,
            'stall_id' => $stall?->id,
            'stall_category_id' => $stall?->stall_category_id,
            'fee_masterlist_id' => $feeMasterlist?->id,
            'fees' => $fees,
            'isNotPaid' => $isNotPaid,
        ]);

        // Fixed table fees
        if (in_array($stall->stall_category_id, [1, 2, 3, 5, 6])) {
            $fee = $feeMasterlist->amount;
            $breakdown[] = (object) ['type' => $feeMasterlist->type, 'amount' => $fee];
            $total += $fee;
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

    public function approveOrReject(Request $request, TableRental $tableRental)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $statusValue = $request->status === 'approved' ? 1 : 2; // 1 = approved, 2 = rejected (adjust as needed)

        $tableRental->status = $statusValue;
        $tableRental->save();

        return response()->json([
            'message' => 'Table rental status updated successfully.',
            'status' => $tableRental->status,
        ]);
    }

    private function disApprove($tableRental, array $payload) {
        $this->updateData($tableRental, ['status' => 3]);
        $PermitController = new PermitController();
        $PermitController->update([
                'status' => 2, //rejected
                'remarks' => $payload['remarks'],
                'assign_to' => 1, // 1 = user
                'is_initial' => false, // set to false when forwarded to user
            ], $tableRental->permit_id);
        // Send SMS or Email notification to user about approval
        // Notification::send($tableRental->user, new tableRentalApproved($tableRental));
    }

    private function approve($tableRental) {
        $user = Auth::user();
        $permit = (object)$tableRental['permits'];

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

            $this->updateData($tableRental, ['status' => 1]);

            $stallController = new StallsListController();
            // Update stall status to 'assigned' (3)
            $stallController->statusUpdate(
                new Request(['status' => 1]),
                Stall::findOrFail((int) $tableRental->stall_id)
            );
        }

        // Send SMS or Email notification to user about approval
        // Notification::send($tableRental->user, new tableRentalApproved($tableRental));
        return [
            'message' => 'Volante rental approved successfully.',
            'tableRental' => $tableRental,
            'permit' => $permit
        ];

    }

    private function updateData(TableRental $tableRental, array $payload)
    {
        $tableRental->fill($payload)->save();

        // If stall_id is changed, update the stall status
        if (isset($payload['stall_id']) && $payload['stall_id'] != $tableRental->stall_id) {
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
                    $requirementData['permit_id'] = $tableRental->permit_id;
                    $requirementController->addRequirements([$requirementData]);
                }
            }
        }

        return $tableRental;
    }
}
