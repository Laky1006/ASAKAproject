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
            return back()->withErrors(['Only regusers or admin can leave reviews.']);
        }

        // Ensure there is a Reguser profile (admin should have one from seeder; still safe)
        $reguser = $user->reguser ?: Reguser::firstOrCreate(['user_id' => $user->id]);
        $reguserId = $reguser->id;

        $serviceId = $request->service_id;

        // Prevent duplicate reviews from the same reguser for the same service
        $existing = Review::where('service_id', $serviceId)
            ->where('reguser_id', $reguserId)
            ->first();

        if ($existing) {
            return back()->withErrors(['You have already reviewed this service.']);
        }

        $review = Review::create([
            'service_id' => $serviceId,
            'reguser_id' => $reguserId,
            'rating'     => $request->rating,
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
