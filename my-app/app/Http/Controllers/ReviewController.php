<?php
// lessons DONE
// providers
// reguser

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Review;
use App\Models\Notification;
use App\Models\Reguser;
use App\Models\ServiceSlot;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'nullable|string|max:1000',
        ]);

        $user = $request->user();

        // Allow only reguser or admin to post reviews
        if (! in_array($user->role, ['reguser', 'admin'])) {
            return back()->withErrors(['Only registered users can leave reviews.']);
        }

        // Ensure there is a Reguser profile (admin should have one from seeder; still safe)
        $reguser = $user->reguser ?: Reguser::firstOrCreate(['user_id' => $user->id]);
        $reguserId = $reguser->id;
        $serviceId = (int) $request->service_id;

        if ($user->role === 'reguser') {
            $hasBookedSlot = ServiceSlot::where('service_id', $serviceId)
                ->where('reguser_id', $reguserId)
                ->where('is_available', false) // means it's booked
                ->exists();

            if (! $hasBookedSlot) {
                return back()->withErrors([
                    'review' => 'You can review this service only after booking at least once.'
                ])->withInput();
            }
        }

        $latest = Review::where('service_id', $serviceId)
            ->where('reguser_id', $reguserId)
            ->latest('created_at')
            ->first();

        if ($latest && $latest->created_at->gt(now()->subDays(2))) {
            $nextDate = $latest->created_at->addDays(2)->toDateString();
            return back()->withErrors([
                'review' => "You can post another review for this service on {$nextDate}."
            ])->withInput();
        }

        $review = Review::create([
            'service_id' => $serviceId,
            'reguser_id' => $reguserId,
            'rating'     => (int) $request->rating,
            'comment'    => $request->comment,
        ]);

        // Update service rating and notify provider
        $service = Service::find($review->service_id);
        if ($service) {
            $service->updateAverageRating();

            Notification::create([
                'user_id'       => $service->provider->user->id,
                'type'          => 'review_left',
                'service_id'    => $service->id,
                'service_title' => $service->title,
                'reguser_id'    => $reguserId,
                'reguser_name'  => $user->name,
            ]);
        }

        return back()->with('success', 'Review posted successfully.');
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $review = Review::findOrFail($id);
    
        // Allow admins OR the owner of the review to delete
        if ($user->role !== 'admin') {
            $reguserId = $user->reguser?->id;
    
            // Prevent non-owners from deleting someone else's review
            if ($review->reguser_id !== $reguserId) {
                abort(403, 'Unauthorized');
            }
        }
    
        $service = $review->service;
        $review->delete();
    
        if ($service) {
            $service->updateAverageRating();
        }
    
        return back()->with('success', 'Review deleted successfully.');
    }
    
}
