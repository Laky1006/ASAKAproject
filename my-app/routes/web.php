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
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminReportController;



Route::get('/', [ServiceController::class, 'index']);

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
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

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
Route::middleware(['auth','admin'])->group(function () {
    // Users list
    Route::get('/admin-panel', [UserController::class, 'index'])->name('admin-panel.dashboard');
    Route::delete('/admin-panel/users/{user}', [UserController::class, 'destroy'])->name('admin-panel.users.destroy');

    // Reports list
    Route::get('/admin-panel/reports', [AdminReportController::class, 'index'])->name('admin-panel.reports.index');
    Route::delete('/admin-panel/reports/{report}', [AdminReportController::class, 'destroy'])->name('admin-panel.reports.destroy');
});


// TOP SECRET PAGE

// simple example
Route::get('/secret', function () {
    return Inertia::render('Secret');
})->middleware(['auth', 'admin']); 


require __DIR__ . '/auth.php';
