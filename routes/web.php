<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Patient\PatientController;
use App\Http\Controllers\Encounter\EncounterController;
use App\Http\Controllers\Encounter\TriageController;
use App\Http\Controllers\Encounter\ConsultationController;
use App\Http\Controllers\EyeClinic\EyeClinicController;
use App\Http\Controllers\Billing\InvoiceController;
use App\Http\Controllers\Billing\PaymentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\SettingsController;

// ── Guest ─────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// ── Authenticated ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'branch.access'])->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/password/change', [ChangePasswordController::class, 'show'])->name('password.change');
    Route::put('/password/change', [ChangePasswordController::class, 'update']);

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // =========================================================
    // SUPER ADMIN ONLY
    // =========================================================
    Route::middleware('role:super_admin')->prefix('admin')->name('admin.')->group(function () {

        Route::resource('branches', BranchController::class)
            ->only(['index', 'create', 'store', 'edit', 'update']);

        Route::get('settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('settings/paystack', [SettingsController::class, 'updatePaystack'])->name('settings.paystack');

        Route::resource('users', UserController::class)->except('show');
        Route::patch('users/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');
        Route::patch('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

        Route::get('audit', fn() => view('placeholder', ['title' => 'Audit Log']))->name('audit.index');
    });

    // =========================================================
    // BRANCH STAFF
    // =========================================================
    Route::middleware('role:receptionist,nurse,branch_admin,optometrist')->group(function () {
        Route::resource('patients', PatientController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update']);
        Route::resource('encounters', EncounterController::class)
            ->only(['index', 'create', 'store', 'show']);
    });

    // Triage — nurse, branch_admin
    Route::middleware('role:nurse,branch_admin')->group(function () {
        Route::get('/triage', [TriageController::class, 'index'])->name('triage.index');
        Route::get('/triage/{encounter}/create', [TriageController::class, 'create'])->name('triage.create');
        Route::post('/triage/{encounter}', [TriageController::class, 'store'])->name('triage.store');
    });

    // Consultations — branch_admin
    Route::middleware('role:branch_admin')->group(function () {
        Route::get('/consultations', [ConsultationController::class, 'index'])->name('consultations.index');
        Route::get('/consultations/{encounter}/edit', [ConsultationController::class, 'edit'])->name('consultations.edit');
        Route::put('/consultations/{encounter}', [ConsultationController::class, 'update'])->name('consultations.update');
        Route::post('/consultations/{encounter}/prescription', [ConsultationController::class, 'storePrescription'])->name('prescription.store');
        Route::get('/pharmacy', fn() => view('placeholder', ['title' => 'Pharmacy']))->name('pharmacy.index');
    });

    // Eye Clinic — optometrist, branch_admin
    Route::middleware('role:optometrist,branch_admin')->group(function () {
        Route::get('/eye-clinic', [EyeClinicController::class, 'index'])->name('eye-clinic.index');
        Route::get('/eye-clinic/{encounter}/notes', [EyeClinicController::class, 'edit'])->name('eye-clinic.notes.edit');
        Route::put('/eye-clinic/{encounter}/notes', [EyeClinicController::class, 'update'])->name('eye-clinic.notes.update');
    });

    // =========================================================
    // BILLING & PAYMENTS — receptionist, branch_admin, optometrist
    // =========================================================
    Route::middleware('role:receptionist,branch_admin,optometrist')->group(function () {

        // Billing (invoice) routes
        // IMPORTANT: static segments MUST come before {invoice} wildcard
        Route::prefix('billing')->name('billing.')->group(function () {
            Route::get('/',                      [InvoiceController::class, 'index'])->name('index');
            Route::post('/',                     [InvoiceController::class, 'store'])->name('store');
            Route::get('/pos',                   [InvoiceController::class, 'pos'])->name('pos');
            Route::get('/encounter/{encounter}', [InvoiceController::class, 'createFromEncounter'])->name('create-encounter');

            // Wildcard last
            Route::get('/{invoice}',             [InvoiceController::class, 'show'])->name('show');
            Route::post('/{invoice}/payment',    [PaymentController::class, 'store'])->name('payment.store');
        });

        // Payment routes
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/',                  [PaymentController::class, 'index'])  ->name('index');
            Route::get('/{payment}',         [PaymentController::class, 'show'])   ->name('show');
            Route::get('/{payment}/receipt', [PaymentController::class, 'receipt'])->name('receipt');
            Route::delete('/{payment}',      [PaymentController::class, 'destroy'])->name('destroy');
        });

    });

    // Inventory
    Route::get('/inventory', fn() => view('placeholder', ['title' => 'Inventory']))->name('inventory.index');

});