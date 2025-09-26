<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\Provider;
use App\Models\ServiceSlot;
use Carbon\Carbon;

class ServiceSeeder extends Seeder
{
    public function run()
{
    $providers = Provider::all();

    if ($providers->isEmpty()) {
        $this->command->info('No providers found. Please seed providers first.');
        return;
    }

    $services = [
        [
            'title' => 'Glow Facial Therapy',
            'description' => "A revitalizing facial designed to deeply cleanse and rehydrate the skin. This treatment combines gentle exfoliation, nourishing masks, and a soothing massage to leave your skin glowing and refreshed. Ideal for tired or dull complexions.",
            'phone' => '+371 20000001',
            'price' => 15,
            'labels' => ['Facial', 'Skincare'],
        ],
        [
            'title' => 'Luxury Gel Manicure',
            'description' => "Treat your hands to a full manicure with nail shaping, cuticle care, and a high-quality gel polish. The treatment is finished with a relaxing hand massage for ultimate softness. Perfect for long-lasting shine and elegance.",
            'phone' => '+371 20000002',
            'price' => 12,
            'labels' => ['Nails', 'Manicure'],
        ],
        [
            'title' => 'Aromatherapy Massage',
            'description' => "A calming full-body massage using essential oils tailored to your needs. Relieve stress, improve circulation, and restore inner balance while enjoying a deeply soothing experience.",
            'phone' => '+371 20000003',
            'price' => 25,
            'labels' => ['Massage', 'Wellness'],
        ],
        [
            'title' => 'Deluxe Pedicure',
            'description' => "Pamper your feet with exfoliation, cuticle care, nail shaping, and a hydrating mask. Finished with a polish of your choice and a gentle foot massage for refreshed, beautiful feet.",
            'phone' => '+371 20000004',
            'price' => 18,
            'labels' => ['Nails', 'Pedicure'],
        ],
        [
            'title' => 'Eyelash Extensions',
            'description' => "Enhance your natural beauty with semi-permanent eyelash extensions. Choose from natural, classic, or dramatic styles for a long-lasting glamorous look without mascara.",
            'phone' => '+371 20000005',
            'price' => 30,
            'labels' => ['Lashes', 'Eyes'],
        ],
        [
            'title' => 'Hair Styling & Blowout',
            'description' => "Get the perfect look for any occasion. Includes professional washing, blow-drying, and styling tailored to your hair type and desired finish—smooth, curly, or voluminous.",
            'phone' => '+371 20000006',
            'price' => 22,
            'labels' => ['Hair', 'Styling'],
        ],
        [
            'title' => 'Brazilian wax',
            'description' => "Enjoy smooth, hair-free skin with our gentle waxing service. Long-lasting results that keep you silky soft and beautiful for weeks.",
            'phone' => '+371 20000007',
            'price' => 60,
            'labels' => ['Waxing', 'Hair Removal'],
        ],
        [
            'title' => 'Hydrating Body Scrub',
            'description' => "Exfoliate and rejuvenate your skin with a luxurious body scrub treatment. Removes dead cells and leaves your skin silky smooth, hydrated, and glowing.",
            'phone' => '+371 20000008',
            'price' => 19,
            'labels' => ['Body', 'Skincare'],
        ],
        [
            'title' => 'Makeup for Special Occasions',
            'description' => "Professional makeup application tailored to your event. From natural elegance to glamorous evening looks, our artists highlight your best features with premium products.",
            'phone' => '+371 20000009',
            'price' => 35,
            'labels' => ['Makeup', 'Beauty'],
        ],
        [
            'title' => 'Spa Package - Relax & Renew',
            'description' => "Indulge in the ultimate spa experience: facial, body scrub, and aromatherapy massage in one session. A full-body renewal that relaxes, refreshes, and restores your glow.",
            'phone' => '+371 20000010',
            'price' => 55,
            'labels' => ['Spa', 'Wellness'],
        ],

    ];

    foreach ($services as $serviceData) {
        $randomProvider = $providers->random();

        $service = Service::create(array_merge($serviceData, [
            'provider_id' => $randomProvider->id,
        ]));

        for ($i = 1; $i <= 3; $i++) {
            ServiceSlot::create([
                'service_id' => $service->id,
                'date' => Carbon::now()->addDays($i)->format('Y-m-d'),
                'time' => '15:00',
                'is_available' => true,
            ]);
        }
    }

    $this->command->info('10 services seeded and assigned to random providers.');
}

}
