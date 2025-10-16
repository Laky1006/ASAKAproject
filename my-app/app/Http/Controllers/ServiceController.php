<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use Inertia\Inertia;
use App\Models\ServiceSlot;
use App\Models\Notification;
use App\Models\Provider;   
use Illuminate\Support\Facades\Auth;
use App\Models\Reguser;



class ServiceController extends Controller
{
    // ---- presenters (reuse in show & providerPreview)
    private function presentService(Service $service, bool $forProviderPreview = false): array
    {
        return [
            'id' => $service->id,
            'title' => $service->title,
            'description' => $service->description,
            'phone' => $service->phone,
            'rating' => $service->rating,
            'banner' => $service->banner,
            'price' => $service->price,

            // IMPORTANT: include booked user info only in provider preview
            'slots' => $service->slots->map(function ($slot) use ($forProviderPreview) {
                return [
                    'date'         => $slot->date->format('Y-m-d'),
                    'time'         => $slot->time,
                    'is_available' => $slot->is_available,
                    'reguser_id'   => $forProviderPreview ? $slot->reguser_id : null,
                    'reguser_name' => $forProviderPreview
                        ? optional(optional($slot->reguser)->user)->name
                        : null,
                ];
            }),
        ];
    }

    private function presentReviews($reviews): array
    {
        return $reviews->map(function ($review) {
            return [
                'id'         => $review->id,
                'rating'     => $review->rating,
                'comment'    => $review->comment,
                'created_at' => $review->created_at->diffForHumans(),
                'reguser_id' => $review->reguser_id,
                'reguser'    => [
                    'name'   => $review->reguser->user->name ?? 'Unknown',
                    'avatar' => $review->reguser->user->avatar ?? null,
                ],
            ];
        })->toArray();
    }

        public function index(Request $request)
{
    $services = Service::with('provider.user')->get();

    $providers = Provider::with([
        'user:id,name',
        'services:id,provider_id,title,price,rating,banner',
    ])->get();

    // Get user's saved services if logged in
    $savedServiceIds = [];
    if (auth()->check()) {
        $savedServiceIds = auth()->user()->savedServices()->pluck('service_id')->toArray();
    }

    return Inertia::render('Home', [
        'services'  => $services,
        'providers' => $providers,
        'auth'      => ['user' => auth()->user()],
        'initialTab' => $request->query('tab', 'services'),
        'savedServiceIds' => $savedServiceIds, // NEW - pass saved service IDs
    ]);
}



    // Gets all the needed things to show on show.vue
    public function show($id)
    {
        $service = Service::with(['slots', 'reviews.reguser.user'])->findOrFail($id);

        return Inertia::render('Services/Show', [
            'service' => $this->presentService($service),
            'reviews' => $this->presentReviews($service->reviews),
            'mode'    => 'public', // <— tell the view this is the public mode
        ]);
    }

    // Provider preview (reuses Services/Show.vue, but in provider mode)
    public function providerPreview(Service $service)
    {
        $user = auth()->user();
        if (!$user || !$user->provider || (int)$user->provider->id !== (int)$service->provider_id) {
            abort(403, 'Unauthorized.');
        }

        // load booked user for each slot
        $service->load(['slots.reguser.user', 'reviews.reguser.user']);

        return Inertia::render('Services/Show', [
            'service' => $this->presentService($service, true),   // include reguser_name
            'reviews' => $this->presentReviews($service->reviews),
            'mode'    => 'provider',
        ]);
    }

    public function providerCancelSlot(Request $request, Service $service)
{
    $user = auth()->user();
    if (!$user || !$user->provider || (int)$user->provider->id !== (int)$service->provider_id) {
        abort(403, 'Unauthorized.');
    }

    $validated = $request->validate([
        'slot_id' => ['nullable', 'integer', 'min:1'],
        'date'    => ['nullable', 'date'],
        'time'    => ['nullable', 'string'], // HH:mm
    ]);

    // Fetch slot (with client relation for notification)
    if (!empty($validated['slot_id'])) {
        $slot = ServiceSlot::with('reguser.user')
            ->where('id', $validated['slot_id'])
            ->where('service_id', $service->id)
            ->whereNotNull('reguser_id')
            ->first();
    } else {
        if (empty($validated['date']) || empty($validated['time'])) {
            return back()->withErrors(['error' => 'No slot specified.']);
        }
        $slot = ServiceSlot::with('reguser.user')
            ->where('service_id', $service->id)
            ->whereDate('date', $validated['date'])
            ->whereTime('time', $validated['time'])
            ->whereNotNull('reguser_id')
            ->first();
    }

    if (!$slot) {
        return back()->withErrors(['error' => 'Booked slot not found.']);
    }

    // Capture info BEFORE freeing
    $clientUserId = optional(optional($slot->reguser)->user)->id; // user who booked
    $noteDate     = $slot->date instanceof \Carbon\Carbon ? $slot->date->format('Y-m-d') : (string) $slot->date;
    $noteTime     = $slot->time;
    $providerName = optional(optional($service->provider)->user)->name ?? 'Provider';

    // Free the slot
    $slot->reguser_id   = null;
    $slot->is_available = true;
    $slot->save();

    // Create a persistent notification for the client (no realtime)
    if ($clientUserId) {
        Notification::create([
            'user_id'       => $clientUserId,
            'type'          => 'booking_canceled_by_provider',
            'service_id'    => $service->id,
            'service_title' => $service->title,
            'reguser_id'    => null,                 // optional: who receives, not who acted
            'reguser_name'  => $providerName,        // who canceled
            'date'          => $noteDate,
            'time'          => $noteTime,
        ]);
    }

    return back()->with('success', 'Booking canceled and slot is now available.');
}



    public function create()
    {
        return Inertia::render('Services/CreateService');
    }

    // works new service creation on provider side
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'available_slots' => 'nullable|array',
            'available_slots.*.date' => 'required_with:available_slots|date',
            'available_slots.*.time' => 'required_with:available_slots|date_format:H:i',
            'banner' => 'nullable|image|max:2048',
            'labels' => 'nullable|array',
            'labels.*' => 'string|max:50',
            'price' => 'nullable|numeric|min:0|max:10000',
        ]);

        $data['labels'] = $request->labels ?? [];

        $provider = auth()->user()->provider;
        if (!$provider) abort(403, 'Only providers can create services.');

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'phone' => $request->phone,
            'price' => $request->price,
            'provider_id' => $provider->id,
        ];

        if ($request->hasFile('banner')) {
            $data['banner'] = $request->file('banner')->store('service-banners', 'public');
        }

        $service = Service::create($data);

        // Save slots
        foreach ($request->available_slots ?? [] as $slot) {
            ServiceSlot::create([
                'service_id' => $service->id,
                'date' => $slot['date'],
                'time' => $slot['time'],
                'is_available' => true,
            ]);
        }

        return redirect()->route('my-services')->with('success', 'Service created successfully.');
    }

    
    // works reguser booking ()
    public function book(Request $request, $serviceId)
{
    if (!auth()->user()?->reguser) {
        session()->flash('debug', 'User is not a reguser');
        return back();
    }

    $reguserId = auth()->user()->reguser->id;

    $service = Service::with('provider.user')->findOrFail($serviceId);

    $slot = ServiceSlot::where('service_id', $serviceId)
        ->where('date', $request->date)
        ->where('time', $request->time)
        ->where('is_available', true)
        ->first();

    if (!$slot) {
        session()->flash('debug', 'Slot not found or already booked');
        return back();
    }

    $slot->is_available = false;
    $slot->reguser_id = $reguserId;
    $slot->save();

    session()->flash('success', 'You have signed up for this service!');
    session()->flash('debug', "Booked slot ID: {$slot->id}, reguser ID: {$reguserId}");

    
    Notification::create([
        'user_id' => $service->provider->user->id,
        'type' => 'service_booked',
        'service_id' => $service->id,
        'service_title' => $service->title,
        'reguser_id' => $reguserId,
        'reguser_name' => auth()->user()->name,
        'date' => $request->date,
        'time' => $request->time,
    ]);

    return back();
}

    // get only the services this tsudent has signed up for

    public function reguserServices()
    {
        $reguserId = auth()->user()->reguser->id;

        $slots = \App\Models\ServiceSlot::with('service.provider.user')
            ->where('reguser_id', $reguserId)
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        $services = $slots->map(function ($slot) {
            $service = $slot->service;
            return [
                'id' => $service->id,
                'title' => $service->title,
                'banner' => $service->banner,
                'provider' => $service->provider->user->name ?? 'Unknown',
                'date' => $slot->date->format('Y-m-d'),
                'time' => $slot->time,
            ];
        });

        return Inertia::render('Services/ReguserServices', [
            'services' => $services,
        ]);
    }

    // cancels services, make them avilable in database again
    public function cancel(Request $request)
{
    $reguserId = auth()->user()?->reguser?->id;

    if (!$reguserId) {
        return back()->withErrors(['error' => 'Only regusers can cancel services.']);
    }

    // Load slot WITH service -> provider -> user so we can notify the provider
    $slot = ServiceSlot::with('service.provider.user')
        ->where('service_id', $request->service_id)
        ->where('date', $request->date)
        ->where('time', $request->time)
        ->where('reguser_id', $reguserId)
        ->first();

    if (!$slot) {
        return back()->withErrors(['error' => 'Booking not found.']);
    }

    // Capture details BEFORE freeing the slot
    $providerUserId = optional(optional($slot->service)->provider)->user->id ?? null;
    $serviceId      = $slot->service_id;
    $serviceTitle   = $slot->service->title ?? 'Service';
    $date           = $slot->date instanceof \Carbon\Carbon ? $slot->date->format('Y-m-d') : (string) $slot->date;
    $time           = $slot->time;
    $cancelingName  = auth()->user()->name; // the client who cancels

    // Free the slot
    $slot->reguser_id = null;
    $slot->is_available = true;
    $slot->save();

    // Notify the provider (if exists)
    if ($providerUserId) {
        Notification::create([
            'user_id'       => $providerUserId,                 // provider's user id (recipient)
            'type'          => 'booking_canceled_by_client',    // NEW type
            'service_id'    => $serviceId,
            'service_title' => $serviceTitle,
            'reguser_id'    => $reguserId,                      // who canceled (optional)
            'reguser_name'  => $cancelingName,                  // who canceled (display)
            'date'          => $date,
            'time'          => $time,
        ]);
    }

    return back()->with('success', 'Service booking canceled.');
}

    // works service editing on provider side
    public function edit($id)
    {
        
        $service = Service::findOrFail($id);

        // Ensure the logged-in provider owns the service
        if ($service->provider_id !== auth()->user()->provider->id) {
            abort(403);
        }

        return Inertia::render('Services/EditService', [
            'service' => [
                'id' => $service->id,
                'title' => $service->title,
                'description' => $service->description,
                'phone' => $service->phone,
                'price' => $service->price,
                'banner' => $service->banner,
                'slots' => $service->slots->map(fn($slot) => [
                    'date' => $slot->date->format('Y-m-d'),
                    'time' => $slot->time,
                ]),
                'labels' => $service->labels ?? [],
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        
        $service = Service::findOrFail($id);

        // Ensure the logged-in provider owns this service
        if ($service->provider_id !== auth()->user()->provider->id) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'phone' => 'required|string|max:20',
            'banner' => 'nullable|image|max:2048',
            'price' => 'nullable|numeric|min:0|max:10000',
        ]);

        $service->title = $request->title;
        $service->description = $request->description;
        $service->phone = $request->phone;
        $service->labels = $request->labels ?? [];
        $service->price = $request->price;

        if ($request->hasFile('banner')) {
            $service->banner = $request->file('banner')->store('service-banners', 'public');
        }

        $service->save();

        // Clear old slots (if any)
        $service->slots()->delete();

        // Add new ones
        foreach ($request->input('available_slots', []) as $slot) {
            $service->slots()->create([
                'date' => $slot['date'],
                'time' => $slot['time'],
                'is_available' => true,
                'reguser_id' => null,
            ]);
        }

        return redirect()->route('my-services')->with('success', 'Service updated.');
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $user = auth()->user();
    
        // Ensure only the owner provider can delete
        if ($user->role !== 'provider' || $user->provider->id !== $service->provider_id) {
            abort(403, 'Unauthorized action.');
        }
    
        $service->delete();
    
        return redirect()->route('my-services')->with('success', 'Service deleted successfully.');
    }


}
