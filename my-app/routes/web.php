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


// Route::get('/', function () {
//     // return Inertia::render('Welcome', [
//     //     'canLogin' => Route::has('login'),
//     //     'canRegister' => Route::has('register'),
//     //     'laravelVersion' => Application::VERSION,
//     //     'phpVersion' => PHP_VERSION,
//     // ]);


//     // npm run dev
//     // php artisan serve

//  // MAKE IMG FOR LESOSN, SHORT DECS, LONG DESC

//     return Inertia::render('Home');
// });

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/', [ServiceController::class, 'index']);

Route::middleware(['auth'])->group(function () {
    Route::post('/reviews', [ReviewController::class, 'store'])->middleware(['auth'])->name('reviews.store');

});

Route::delete('/reviews/{id}', [ReviewController::class, 'destroy'])
    ->middleware(['auth'])
    ->name('reviews.destroy');

Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index')->middleware('auth');


Route::get('/about', function () {
    return Inertia::render('About', [
        'auth' => ['user' => auth()->user()],
    ]);
})->name('about');

// Route::get('/my-services', function () {
//     $user = auth()->user();

//     if ($user->role === 'provider') {
//         return Inertia::render('Services/ProviderServices');
//     }

//     if ($user->role === 'reguser') {
//         return Inertia::render('Services/ReguserServices');
//     }

//     abort(403);
// })->middleware(['auth'])->name('my-services');

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
})->middleware(['auth'])->name('my-services');

//Route::post('/services', [ServiceController::class, 'store'])->name('services.store');

    // Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');

    Route::middleware('auth')->group(function () {
        Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    });


Route::get('/services-create', function () {
    return Inertia::render('Services/CreateService');
})->name('services.create');
Route::get('/services/{id}', [ServiceController::class, 'show'])->name('services.show');

Route::delete('/services/{id}', [ServiceController::class, 'destroy'])->middleware(['auth'])->name('services.destroy');



Route::put('/services/{id}', [ServiceController::class, 'update'])->middleware(['auth'])->name('services.update');

Route::post('/services/{id}/book', [ServiceController::class, 'book'])->middleware(['auth'])->name('services.book');

Route::post('/services/cancel', [ServiceController::class, 'cancel'])->middleware(['auth'])->name('services.cancel');

Route::get('/services/{id}/edit', [ServiceController::class, 'edit'])->middleware(['auth'])->name('services.edit');



require __DIR__.'/auth.php';