<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\Review;
use App\Models\Reguser;
use Illuminate\Support\Str;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $regusers = Reguser::with('user')->get();
        $services = Service::all();

        if ($regusers->isEmpty() || $services->isEmpty()) {
            $this->command->info('No regusers or services found. Seed those first.');
            return;
        }

        foreach ($services as $service) {
            // picks some regusers to review this service
            $reviewers = $regusers->random(min($regusers->count(), rand(2, 4)));

            foreach ($reviewers as $reguser) {
                // avoids duplicates
                if (Review::where('service_id', $service->id)->where('reguser_id', $reguser->id)->exists()) {
                    continue;
                }

                Review::create([
                    'service_id' => $service->id,
                    'reguser_id' => $reguser->id,
                    'rating' => rand(3, 5),
                    'comment' => rand(0, 1) ? $this->randomComment() : null,
                ]);
            }

            $average = Review::where('service_id', $service->id)->avg('rating');
            $service->rating = round($average, 1);
            $service->save();
        }
    }

    private function randomComment(): string
    {
        $samples = [
            "Great service! Very informative and well-structured.",
            "The Provider was kind and helpful. Highly recommend.",
            "I learned a lot in a short time. :)",
            "Could be improved with more examples, but still good.",
            "Excellent session! Looking forward to more.",
            "very bad. "
        ];

        return $samples[array_rand($samples)];
    }
}
