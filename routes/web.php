<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EscrowController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DisputeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ControlNumberController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\MakerCheckerController;
use App\Http\Controllers\ITSupportController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\GuestTransactionController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Homepage
Route::get('/', function () {
    // Check if user is logged in and redirect based on role
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->role === 'it_support') {
            return redirect()->route('it_support.dashboard');
        } elseif (in_array($user->role, ['maker', 'checker'])) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

// Keep the original dashboard route for buyers and sellers
Route::get('/dashboard', function () {
    // Redirect admin users to admin dashboard
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->role === 'it_support') {
            return redirect()->route('it_support.dashboard');
        } elseif (in_array($user->role, ['maker', 'checker'])) {
            return redirect()->route('admin.dashboard');
        }
    }
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// Guest transaction routes
Route::prefix('guest')->name('guest.')->group(function () {
    Route::get('/verify', [GuestTransactionController::class, 'showVerifyForm'])->name('verify');
    Route::post('/verify', [GuestTransactionController::class, 'verifyControlNumber'])->name('verify.post');
    Route::get('/payment/{token}', [GuestTransactionController::class, 'showPaymentForm'])->name('payment');
    Route::post('/payment/{token}', [GuestTransactionController::class, 'processPayment'])->name('payment.post');
    Route::get('/success/{transaction_id}', [GuestTransactionController::class, 'showTransactionSuccess'])->name('success');
    
    // Claiming guest transactions after login requires authentication
    Route::middleware('auth')->group(function () {
        Route::get('/claim/{token}', [GuestTransactionController::class, 'claimTransaction'])->name('claim');
    });
});

// Auth routes
Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    
    // Escrow routes
    Route::prefix('escrow')->name('escrow.')->group(function () {
        Route::get('/', [EscrowController::class, 'index'])->name('index');
        Route::get('/create', [EscrowController::class, 'create'])->name('create');
        Route::post('/', [EscrowController::class, 'store'])->name('store');
        Route::get('/{escrowId}', [EscrowController::class, 'show'])->name('show');
    });
    
    // Payment routes
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::post('/{escrowId}/deposit', [PaymentController::class, 'deposit'])->name('deposit');
        Route::post('/{escrowId}/release', [PaymentController::class, 'release'])->name('release');
    });
    
    // Shipment routes
    Route::prefix('shipments')->name('shipments.')->group(function () {
        Route::put('/{escrowId}', [ShipmentController::class, 'update'])->name('update');
        Route::post('/{escrowId}/confirm', [ShipmentController::class, 'confirm'])->name('confirm');
    });
    
    // Dispute routes
    Route::prefix('disputes')->name('disputes.')->group(function () {
        Route::get('/', [DisputeController::class, 'index'])->name('index');
        Route::get('/{escrowId}/create', [DisputeController::class, 'create'])->name('create');
        Route::post('/{escrowId}', [DisputeController::class, 'store'])->name('store');
        Route::get('/{disputeId}/show', [DisputeController::class, 'show'])->name('show');
        Route::get('/{disputeId}/resolve', [DisputeController::class, 'resolveForm'])->name('resolve');
        Route::post('/{disputeId}/resolve', [DisputeController::class, 'resolve'])->name('update');
    });
    
    // Control Number routes
    Route::prefix('control-numbers')->name('control-numbers.')->group(function () {
        Route::get('/', [ControlNumberController::class, 'index'])->name('index');
        Route::get('/create', [ControlNumberController::class, 'create'])->name('create');
        Route::post('/', [ControlNumberController::class, 'store'])->name('store');
        Route::get('/{controlNumber}', [ControlNumberController::class, 'show'])->name('show');
        Route::post('/verify', [ControlNumberController::class, 'verify'])->name('verify');
    });
    
    // Audit Log routes (moved outside of admin prefix to be available at top level)
    Route::prefix('audit-log')->name('audit-log.')->middleware(['auth'])->group(function () {
        Route::get('/', [AuditLogController::class, 'index'])->name('index');
        Route::get('/{id}', [AuditLogController::class, 'show'])->name('show');
    });
    
    // Admin routes
    Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // User management
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::post('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::post('/users/{id}/change-role', [AdminController::class, 'changeUserRole'])->name('users.change-role');
        Route::post('/users/{id}/suspend', [AdminController::class, 'suspendUser'])->name('users.suspend');
        Route::post('/users/{id}/delete', [AdminController::class, 'deleteUser'])->name('users.delete');
        
        // Transaction management
        Route::get('/transactions', [AdminController::class, 'transactions'])->name('transactions');
        
        // Dispute management
        Route::get('/disputes', [AdminController::class, 'disputes'])->name('disputes');
        Route::get('/disputes/{id}', [AdminController::class, 'showDispute'])->name('disputes.show');
        Route::post('/disputes/{id}/resolve', [AdminController::class, 'resolveDispute'])->name('disputes.resolve');
        
        // Reports
        Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
        
        // Maker-Checker routes
        Route::prefix('maker-checker')->name('maker-checker.')->group(function () {
            Route::get('/', [MakerCheckerController::class, 'index'])->name('index');
            Route::get('/my-actions', [MakerCheckerController::class, 'myActions'])->name('my-actions');
            Route::get('/{actionId}', [MakerCheckerController::class, 'show'])->name('show');
            Route::post('/{actionId}/approve', [MakerCheckerController::class, 'approve'])->name('approve');
            Route::post('/{actionId}/reject', [MakerCheckerController::class, 'reject'])->name('reject');
        });
    });
    
    // Photo evidence routes
    Route::post('/escrow/{escrowId}/photos', [PhotoController::class, 'upload'])->name('photos.upload');
    Route::delete('/photos/{photoId}', [PhotoController::class, 'delete'])->name('photos.delete');
});

// IT Support routes (only for it_support role)
Route::prefix('it-support')->name('it_support.')->middleware(['auth'])->group(function () {
    Route::get('/', [ITSupportController::class, 'dashboard'])->name('dashboard');
    
    // Staff management
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/', [ITSupportController::class, 'staffList'])->name('index');
        Route::get('/create', [ITSupportController::class, 'createStaff'])->name('create');
        Route::post('/', [ITSupportController::class, 'storeStaff'])->name('store');
        Route::get('/{id}/edit', [ITSupportController::class, 'editStaff'])->name('edit');
        Route::put('/{id}', [ITSupportController::class, 'updateStaff'])->name('update');
        Route::post('/{id}/toggle-status', [ITSupportController::class, 'toggleStaffStatus'])->name('toggle-status');
        Route::get('/{id}/reset-password', [ITSupportController::class, 'showResetPassword'])->name('reset-password');
        Route::post('/{id}/reset-password', [ITSupportController::class, 'resetPassword'])->name('update-password');
        Route::post('/generate-password', [ITSupportController::class, 'generatePassword'])->name('generate-password');
    });
    
    // Audit log
    Route::get('/audit-log', [ITSupportController::class, 'auditLog'])->name('audit-log');
});

require __DIR__.'/auth.php';