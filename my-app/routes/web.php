<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ReportController;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Admin\AdminPanelController;
use App\Http\Controllers\ProviderController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;



// verification
// 2.1 Built-in auth routes + verification
Auth::routes(['verify' => true]);  // requires laravel/ui (you installed earlier)

// 2.2 The "check your email" screen (served by Inertia/Vue)
Route::get('/email/verify', function () {
    return Inertia::render('Auth/VerifyEmail', [
        'status' => session('status'), // <-- passes the flash status to your Vue prop
    ]);
})->middleware('auth')->name('verification.notice');

// 2.3 Handle the signed link from the email
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // sets email_verified_at
    return redirect()->route('dashboard');
})->middleware(['auth', 'signed'])->name('verification.verify');

// 2.4 Resend verification email (this matches route('verification.send') in your Vue)
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', 'verification-link-sent'); // your Vue checks this
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// 2.5 Protect private area: only verified users allowed
Route::middleware(['auth','verified'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
});

// Route::get('/', [ServiceController::class, 'index']);
Route::get('/', [\App\Http\Controllers\ServiceController::class, 'index'])->name('home');

Route::get('/saved-services', [App\Http\Controllers\SavedServiceController::class, 'index'])->name('saved-services.index');

Route::get('/dashboard', fn () => Inertia::render('Dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/about', function () {
    return Inertia::render('About', [
        'auth' => ['user' => auth()->user()],
    ]);
})->name('about');

/**
 * Authenticated routes
 */
Route::middleware(['auth', 'verified'])->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Saved services & folder management
    Route::get('/saved-services', [App\Http\Controllers\SavedServiceController::class, 'index'])->name('saved-services.index');
    Route::post('/saved-services', [App\Http\Controllers\SavedServiceController::class, 'store'])->name('saved-services.store');
    Route::delete('/saved-services', [App\Http\Controllers\SavedServiceController::class, 'destroy'])->name('saved-services.destroy');
    Route::post('/saved-services/folders', [App\Http\Controllers\SavedServiceController::class, 'createFolder'])->name('saved-services.folders.create');
    Route::patch('/saved-services/folders/rename', [App\Http\Controllers\SavedServiceController::class, 'renameFolder'])->name('saved-services.folders.rename');
    Route::delete('/saved-services/folders/delete', [App\Http\Controllers\SavedServiceController::class, 'deleteFolder'])->name('saved-services.folders.delete');
    Route::patch('/saved-services/move', [App\Http\Controllers\SavedServiceController::class, 'moveToFolder'])->name('saved-services.move');

        // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

    // provider preview 
    Route::get('/provider/services/{service}/preview',
        [App\Http\Controllers\ServiceController::class, 'providerPreview']
    )->name('services.provider.preview'); 

    Route::post(
        '/provider/services/{service}/slots/cancel',
        [App\Http\Controllers\ServiceController::class, 'providerCancelSlot']
    )->name('services.provider.cancel');


    // My services (provider/reguser split)
    Route::get('/my-services', function () {
        $user = Auth::user();

        if ($user->role === 'provider') {
            $providerId = $user->provider->id ?? null;
            if (!$providerId) {
                abort(403, 'Provider record not found.');
            }

            $services = Service::where('provider_id', $providerId)->get();

            return Inertia::render('Services/ProviderServices', [
                'services' => $services,
                'auth' => ['user' => $user],
            ]);
        }

        if ($user->role === 'reguser') {
            return app(ServiceController::class)->reguserServices();
        }

        abort(403);
    })->name('my-services');

    // Services (auth-required actions)
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::delete('/services/{id}', [ServiceController::class, 'destroy'])->name('services.destroy');
    Route::put('/services/{id}', [ServiceController::class, 'update'])->name('services.update');
    Route::post('/services/{id}/book', [ServiceController::class, 'book'])->name('services.book');
    Route::post('/services/cancel', [ServiceController::class, 'cancel'])->name('services.cancel');
    Route::get('/services/{id}/edit', [ServiceController::class, 'edit'])->name('services.edit');


    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');

});

// Services (public)
Route::get('/services-create', fn () => Inertia::render('Services/CreateService'))->name('services.create');
Route::get('/services/{id}', [ServiceController::class, 'show'])->name('services.show');

// Admin-only
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/admin-panel', [AdminPanelController::class, 'index'])
        ->name('admin-panel.dashboard');

    Route::delete('/admin-panel/users/{user}', [AdminPanelController::class, 'destroyUser'])
        ->name('admin-panel.users.destroy');
    
    Route::delete('/admin-panel/services/{service}', [AdminPanelController::class, 'destroyService'])
        ->name('admin-panel.services.destroy');
    

    Route::delete('/admin-panel/reports/{report}', [AdminPanelController::class, 'destroyReport'])
        ->name('admin-panel.reports.destroy');
});

Route::middleware(['auth', 'admin'])->post('/admin/run-inactive-users', function () {
    // Run the command
    Artisan::call('notify:inactive-users');

    // Return a minimal JSON response
    return response()->json([
        'message' => 'Emails sent successfully!',
    ]);
})->name('admin.runInactiveUsers');


// TOP SECRET PAGE

// simple example
Route::get('/secret', function () {
    return Inertia::render('Secret');
})->middleware(['auth', 'admin']); 


//testing page
Route::get('/tests', function () {
    return Inertia::render('Test'); // points to resources/js/Pages/Test.vue
});


// Providers
Route::get('/providers', [ProviderController::class, 'index'])->name('providers.index');
Route::get('/providers/{id}', [ProviderController::class, 'show'])->name('providers.show');

require __DIR__ . '/auth.php';



