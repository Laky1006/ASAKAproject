<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\UserController;

// -------------------- Dashboard --------------------
Route::get('/dashboard', function () {
    // Shows Dashboard.vue but only for logged-in + email-verified users
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// -------------------- Profile (only for logged in) --------------------
Route::middleware('auth')->group(function () {
    // Edit profile page
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    // Update profile (PATCH = partial update)
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Delete account
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// -------------------- Home page --------------------
Route::get('/', [ServiceController::class, 'index']);

// -------------------- Reviews --------------------
Route::middleware(['auth'])->group(function () {
    // Add a review (must be logged in)
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});

// Delete a review (must be logged in)
Route::delete('/reviews/{id}', [ReviewController::class, 'destroy'])
    ->middleware(['auth'])
    ->name('reviews.destroy');

// -------------------- Notifications --------------------
Route::get('/notifications', [NotificationController::class, 'index'])
    ->name('notifications.index')
    ->middleware('auth');

// -------------------- About --------------------
Route::get('/about', function () {
    // Renders About.vue and also passes the logged-in user to it
    return Inertia::render('About', [
        'auth' => ['user' => auth()->user()],
    ]);
})->name('about');

// -------------------- My Services (depends on user role) --------------------
// Route::get('/my-services', function () {
//     $user = Auth::user();

//     if ($user->role === 'provider') {
//         // Provider: fetch services linked to provider_id
//         $providerId = $user->provider->id ?? null;

//         if (!$providerId) {
//             abort(403, 'Provider record not found.');
//         }

//         $services = Service::where('provider_id', $providerId)->get();

//         return Inertia::render('Services/ProviderServices', [
//             'services' => $services,
//             'auth' => ['user' => $user],
//         ]);
//     }

//     if ($user->role === 'reguser') {
//         // Reguser: delegate to controller method
//         return app(ServiceController::class)->reguserServices();
//     }

//     // Any other role: deny access
//     abort(403);
// })->middleware(['auth'])->name('my-services');

// -------------------- Services CRUD --------------------

// Create a service (POST) → only logged-in
Route::middleware('auth')->group(function () {
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
});

// Show service creation form
Route::get('/services-create', function () {
    return Inertia::render('Services/CreateService');
})->name('services.create');

// Show a single service
Route::get('/services/{id}', [ServiceController::class, 'show'])->name('services.show');

// Delete a service
Route::delete('/services/{id}', [ServiceController::class, 'destroy'])
    ->middleware(['auth'])
    ->name('services.destroy');

// Update a service
Route::put('/services/{id}', [ServiceController::class, 'update'])
    ->middleware(['auth'])
    ->name('services.update');

// Book a service
Route::post('/services/{id}/book', [ServiceController::class, 'book'])
    ->middleware(['auth'])
    ->name('services.book');

// Cancel a booked service
Route::post('/services/cancel', [ServiceController::class, 'cancel'])
    ->middleware(['auth'])
    ->name('services.cancel');

// Edit service page (form)
Route::get('/services/{id}/edit', [ServiceController::class, 'edit'])
    ->middleware(['auth'])
    ->name('services.edit');

// -------------------- Admin-only area --------------------
Route::middleware(['auth','admin'])->group(function () {
    // Admin dashboard (only users with role = admin)
    Route::get('/admin', [UserController::class, 'index'])->name('admin.dashboard');

    // In future: add more admin routes here (e.g. /admin/users, /admin/services)
    // Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');

});

// -------------------- Auth scaffolding (Breeze/Jetstream/etc.) --------------------
require __DIR__.'/auth.php';
