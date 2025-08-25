<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Cache\RateLimiter;

use App\Http\Middleware\ValidateEmailAndAge;
use App\Http\Middleware\ValidateUser;
use App\Http\Middleware\CheckUserRole;
use App\Http\Middleware\ValidateVolanteDates;

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UserTypeController;
use App\Http\Controllers\SubAdminTypeController;
use App\Http\Controllers\StallsListController;
use App\Http\Controllers\StallRentalController;
use App\Http\Controllers\VolanteRentalController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StallTypeController;
use App\Http\Controllers\RequirementListController;
use App\Http\Controllers\VerifyEmailController;
use App\Http\Controllers\FeeMasterlistController;
use App\Http\Controllers\RequirementController;
use App\Http\Controllers\PaymentController;

// home page
Route::get('/', [DashboardController::class, 'index'])
    ->name('home')
    ->middleware('guest');
 
// login routes
Route::get('login', [AccountsController::class, 'create'])
    ->name('login')
    ->middleware('guest');

Route::post('register', [UserController::class, 'register'])
    ->name('register')
    ->middleware('guest');

Route::post('login', [AccountsController::class, 'store'])
    ->name('login.store')
    ->middleware('guest');

Route::post('logout', [AccountsController::class, 'destroy'])
    ->name('logout')
    ->middleware('auth');   
Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed'])
    ->name('verification.verify');

Route::post('/email/verification-notification/{user}', [VerifyEmailController::class, 'sendVerificationEmail'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');   

/**
 * User View
 */
Route::middleware(['auth', CheckUserRole::class . ':3'])->group(function () {
    Route::get('dashboard', [UserController::class, 'index'])
        ->name('dashboard')
        ->middleware('auth');
    Route::get('my-applications/stall-rental-permits', [StallRentalController::class, 'index'])->name('applications.stalls.index');
    Route::get('my-applications/stall-rental-permits/{stallRental}/edit', [StallRentalController::class, 'edit'])->name('applications.stalls.edit');
    Route::middleware([ValidateVolanteDates::class])->group(function () {
        Route::post('my-applications/stall-rental-permits', [StallRentalController::class, 'store'])->name('applications.stalls.store');
    });
    Route::get('my-applications/stall-rental-permits/create', [StallRentalController::class, 'create'])->name('applications.stalls.create');
    Route::put('my-applications/stall-rental-permits/{stallRental}/update', [StallRentalController::class, 'update'])->name('applications.stalls.update');
    // Route::get('my-applications/business-permits', [BusinessController::class, 'index'])->name('applications.business.permit');

    Route::get('my-applications/volante-rental-permits', [VolanteRentalController::class, 'index'])->name('applications.volantes.index');
    Route::get('my-applications/volante-rental-permits/create', [VolanteRentalController::class, 'create'])->name('applications.volantes.create');
    Route::middleware([ValidateVolanteDates::class])->group(function () {
        Route::get('my-applications/volante-rental-permits/validate', [VolanteRentalController::class, 'fieldValidator'])
        ->name('applications.volantes.validate');
    });
    Route::get('my-applications/volante-rental-permits/validate-payment', [PaymentController::class, 'fieldValidator'])
        ->name('applications.volantes.validate-payment');
    Route::get('my-applications/volante-rental-permits/validate-requirements', [RequirementController::class, 'fieldValidator'])
        ->name('applications.volantes.validate-requirements');
    Route::post('my-applications/volante-rental-permits', [VolanteRentalController::class, 'store'])
        ->name('applications.volantes.store')->middleware('auth');
    Route::get('my-applications/volante-rental-permits/{volanteRental}/edit', [VolanteRentalController::class, 'edit'])->name('applications.volantes.edit')->middleware('auth');
    Route::put('my-applications/volante-rental-permits/{volanteRental}/update', [VolanteRentalController::class, 'update'])->name('applications.volantes.update')->middleware('auth');
});

/**
 * User View
 */

// Admin
Route::middleware(['auth', CheckUserRole::class . ':1'])->group(function () {
    Route::get('admin/dashboard', [AccountsController::class, 'index'])
        ->name('admin.dashboard');
        
    Route::get('admin/departments', [UserController::class, 'index'])
        ->name('admin.departments');

    Route::get('admin/users', [UserController::class, 'index'])
        ->name('admin.users.all');

    Route::get('admin/users/create', [UserController::class, 'create'])
        ->name('admin.users.create');

    Route::middleware(ValidateEmailAndAge::class)->group(function () {
        Route::post('admin/users', [UserController::class, 'store'])->name('admin.users.store')->middleware('auth');
    });

    Route::get('admin/users/update/{user}/edit', [UserController::class, 'edit'])
        ->name('admin.users.edit');

    Route::put('admin/users/update/{user}', [UserController::class, 'update'])
        ->name('admin.users.update');

    Route::put('admin/users/approve/{user}', [UserController::class, 'approverOrRejected'])
        ->name('admin.users.approve');

    Route::get('admin/users/user-types', [UserTypeController::class, 'index'])
        ->name('users.user_type');

    Route::get('admin/users/roles', [RoleController::class, 'index'])
        ->name('users.roles');

    Route::get('admin/users/sub-admin-types', [SubAdminTypeController::class, 'index'])
        ->name('users.admintype');

    Route::get('admin/departments', [DepartmentController::class, 'index'])
        ->name('admin.departments');

    // applications
    Route::get('admin/applications/stalls', [StallRentalController::class, 'index'])->name('admin.applications.stalls');
    // Route::get('admin/applications/business-permit', [BusinessController::class, 'index'])->name('admin.applications.business.permit');
    Route::get('admin/applications/volantes', [VolanteRentalController::class, 'index'])->name('admin.applications.volantes');
    // Settings
    //stalls
    Route::get('admin/system-setting/stalls', [StallsListController::class, 'index'])->name('admin.stalls')->middleware('auth');
    Route::post('admin/system-setting/stalls', [StallsListController::class, 'store'])->name('admin.stalls.store')->middleware('auth');
    Route::get('admin/system-setting/stalls/create', [StallsListController::class, 'create'])->name('admin.stalls.create');

    Route::get('admin/system-setting/stalls/{stall}/edit', [StallsListController::class, 'edit'])->name('admin.stalls.edit');
    Route::put('admin/system-setting/stalls/{stall}/update', [StallsListController::class, 'update'])->name('admin.stalls.update');
    Route::get('admin/system-setting/stalls/{stall}', [StallsListController::class, 'getSingleRecord'])->name('admin.stalls.single');

    //volante-type
    Route::get('admin/system-setting/stall-types', [StallTypeController::class, 'index'])->name('admin.stall.type');
    Route::get('admin/system-setting/stall-types/create', [StallTypeController::class, 'create'])->name('admin.stall.type.create');
    Route::post('admin/system-setting/stall-types', [StallTypeController::class, 'store'])->name('admin.stall.type.store');
    Route::get('admin/system-setting/stall-types/{stallsCategories}/edit', [StallTypeController::class, 'edit'])->name('admin.stall.type.edit');
    Route::put('admin/system-setting/stall-types/{stallsCategories}/update', [StallTypeController::class, 'update'])->name('admin.stall.type.update');

    Route::get('admin/system-setting/requirements', [RequirementListController::class, 'index'])->name('admin.requirements.list');
    Route::get('admin/system-setting/requirements/create', [RequirementListController::class, 'create'])->name('admin.requirements.create');
    Route::post('admin/system-setting/requirements', [RequirementListController::class, 'store'])->name('admin.requirements.store');
    Route::get('admin/system-setting/requirements/{requirementsList}/edit', [RequirementListController::class, 'edit'])->name('admin.requirements.edit');
    Route::put('admin/system-setting/requirements/{requirementsList}/update', [RequirementListController::class, 'update'])->name('admin.requirements.update');
    Route::get('admin/system-setting/fees', [FeeMasterlistController::class, 'index'])->name('admin.fee.masterlist');
    Route::get('admin/system-setting/fees/{feeMasterlist}/edit', [FeeMasterlistController::class, 'edit'])->name('admin.fees.edit');
    Route::put('admin/system-setting/fees/{feeMasterlist}/update', [FeeMasterlistController::class, 'update'])->name('admin.fees.update');
});
/**
 * Admin
 */


/**
 * Department Admin
 */
Route::middleware(['auth', CheckUserRole::class . ':2'])->group(function () {
    Route::get('department/dashboard', [UserController::class, 'index'])
        ->name('department.dashboard')
        ->middleware('auth');
    Route::get('department/applications/volante-rental-permits/{volanteRental}', [VolanteRentalController::class, 'show'])
        ->name('department.applications.volantes.show')
        ->middleware('auth');
    Route::get('department/applications/volante-rental-permits', [VolanteRentalController::class, 'index'])->name('department.applications.volantes')->middleware('auth');
    Route::put('department/applications/volante-rental-permits/{volanteRental}/approve', [VolanteRentalController::class, 'approveVolante'])
        ->name('department.applications.volantes.approve')
        ->middleware('auth');

    Route::put('department/applications/volante-rental-permits/{volanteRental}/reject', [VolanteRentalController::class, 'rejectolante'])
        ->name('department.applications.volantes.reject')
        ->middleware('auth');

    Route::get('department/applications/stall-rental-permits', [StallRentalController::class, 'index'])->name('department.applications.stalls')->middleware('auth');
    Route::put('department/applications/stall-rental-permits/{stallRental}/approve', [StallRentalController::class, 'approveRental'])
        ->name('department.applications.stalls.approve')
        ->middleware('auth');

    Route::put('department/applications/stall-rental-permits/{stallRental}/reject', [StallRentalController::class, 'rejectRental'])
        ->name('department.applications.stalls.reject')
        ->middleware('auth');
});