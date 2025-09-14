<?php
// lesons DONE
//providers
// reguser

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Review;
use App\Models\Notification;

class ReviewController extends Controller
{
    public function store(Request $request)
{
    $request->validate([
        'service_id' => 'required|exists:services,id',
        'rating' => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string|max:1000',
    ]);

    $reguserId = auth()->user()->reguser->id ?? null;

    if (!$reguserId) {
        return back()->withErrors(['Only regusers can leave reviews.']);
    }

    $serviceId = $request->service_id;

    // Prevent duplicate reviews
    $existing = Review::where('service_id', $serviceId)
        ->where('reguser_id', $reguserId)
        ->first();

    if ($existing) {
        return back()->withErrors(['You have already reviewed this service.']);
    }

    $review = Review::create([
        'service_id' => $serviceId,
        'reguser_id' => $reguserId,
        'rating' => $request->rating,
        'comment' => $request->comment,
    ]);

    $service = Service::find($review->service_id);
    $service->updateAverageRating();

    Notification::create([
    'user_id' => $service->provider->user->id,
    'type' => 'review_left',
    'service_id' => $service->id,
    'service_title' => $service->title,
    'reguser_id' => auth()->user()->reguser->id ?? null,
    'reguser_name' => auth()->user()->name,
]);

    return back()->with('success', 'Review posted successfully.');
}

public function destroy($id)
{
    $review = Review::findOrFail($id);

    // Ensure the logged-in user is the one who posted it
    if ($review->reguser_id !== auth()->user()->reguser->id) {
        abort(403, 'Unauthorized to delete this review.');
    }

    $service = $review->service; 
    $review->delete();
    $service->updateAverageRating();

    return back()->with('success', 'Review deleted.');
}


}
