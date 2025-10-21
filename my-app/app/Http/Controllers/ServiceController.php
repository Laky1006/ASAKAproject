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
    
        $reguser = auth()->user()->reguser;
    
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
    
        // Book the slot
        $slot->is_available = false;
        $slot->reguser_id = $reguser->id;
        $slot->save();
    
        // Update the user's last booking date
        $reguser->last_booking_at = now();
        $reguser->save();
    
        // Notify provider
        Notification::create([
            'user_id'       => $service->provider->user->id,
            'type'          => 'service_booked',
            'service_id'    => $service->id,
            'service_title' => $service->title,
            'reguser_id'    => $reguser->id,
            'reguser_name'  => auth()->user()->name,
            'date'          => $request->date,
            'time'          => $request->time,
        ]);
    
        session()->flash('success', 'You have signed up for this service!');
        session()->flash('debug', "Booked slot ID: {$slot->id}, reguser ID: {$reguser->id}");
    
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
                    'available' => (bool) $slot->is_available,
                ]),
                'labels' => $service->labels ?? [],
            ],
        ]);
    }

    public function update(Request $request, $id)
{
    // Load service with slots for diffing
    $service = Service::with('slots')->findOrFail($id);

    // Ensure the logged-in provider owns this service
    if (!auth()->user()?->provider || $service->provider_id !== auth()->user()->provider->id) {
        abort(403);
    }

    // Validate fields + incoming slots
    $validated = $request->validate([
        'title'                  => 'required|string|max:255',
        'description'            => 'required|string',
        'phone'                  => 'required|string|max:20',
        'banner'                 => 'nullable|image|max:2048',
        'price'                  => 'nullable|numeric|min:0|max:10000',
        'labels'                 => 'nullable|array|max:10',
        'labels.*'               => 'string|max:50',
        'available_slots'        => 'array',
        'available_slots.*.date' => 'required|date_format:Y-m-d',
        'available_slots.*.time' => 'required|string', // "09:00" / "09:00:00"
    ]);

    // Build a consistent key "YYYY-mm-dd HH:ii"
    $slotKey = function ($date, $time) {
        $d = is_object($date) && method_exists($date, 'format') ? $date->format('Y-m-d') : (string) $date;
        $t = trim((string) $time);
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $t))        $t = substr($t, 0, 5); // 09:00:00 -> 09:00
        elseif (preg_match('/^\d{1}:\d{2}$/', $t))          $t = '0'.$t;           // 9:00 -> 09:00
        elseif (!preg_match('/^\d{2}:\d{2}$/', $t)) { try { $t = \Carbon\Carbon::parse($t)->format('H:i'); } catch (\Throwable $e) {} }
        return "{$d} {$t}";
    };

    // Normalize incoming slots and dedupe
    $incoming = collect($request->input('available_slots', []))
        ->map(function ($s) use ($slotKey) {
            $date = $s['date'];
            $time = trim($s['time']);
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time))      $time = substr($time, 0, 5);
            elseif (preg_match('/^\d{1}:\d{2}$/', $time))        $time = '0'.$time;
            return ['date' => $date, 'time' => $time, 'key' => $slotKey($date, $time)];
        })
        ->unique('key')
        ->values();

    \Illuminate\Support\Facades\DB::transaction(function () use ($service, $request, $validated, $incoming, $slotKey) {
        /* ------------ Update main fields ------------ */
        $service->title       = $validated['title'];
        $service->description = $validated['description'];
        $service->phone       = $validated['phone'];
        $service->labels      = $request->input('labels', []);
        $service->price       = $request->input('price');
        if ($request->hasFile('banner')) {
            $service->banner = $request->file('banner')->store('service-banners', 'public');
        }
        $service->save();

        /* ------------ Diff slots (with block) -------- */
        $existing      = $service->slots; // eager-loaded
        $existingByKey = $existing->mapWithKeys(fn ($slot) => [ $slotKey($slot->date, $slot->time) => $slot ]);

        $incomingKeys = $incoming->pluck('key')->all();
        $incomingSet  = array_flip($incomingKeys);

        // A) BLOCK: provider tried to remove a booked slot
        $blocked = $existing->filter(function ($slot) use ($incomingSet, $slotKey) {
            $key     = $slotKey($slot->date, $slot->time);
            $removed = !isset($incomingSet[$key]);
            $booked  = ($slot->is_available === false) || !is_null($slot->reguser_id);
            return $removed && $booked;
        });

        if ($blocked->isNotEmpty()) {
            // Build a friendly error list (e.g., "2025-11-03 09:00, 2025-11-04 10:00")
            $times = $blocked->map(fn($s) => $slotKey($s->date, $s->time))->implode(', ');
            throw \Illuminate\Validation\ValidationException::withMessages([
                'available_slots' => ["You cannot remove booked time(s): {$times}."],
            ]);
        }

        // B) Delete only removed & unbooked
        $toDelete = $existing->filter(function ($slot) use ($incomingSet, $slotKey) {
            $key     = $slotKey($slot->date, $slot->time);
            $missing = !isset($incomingSet[$key]);
            $booked  = ($slot->is_available === false) || !is_null($slot->reguser_id);
            return $missing && !$booked;
        });

        if ($toDelete->isNotEmpty()) {
            \App\Models\ServiceSlot::whereIn('id', $toDelete->pluck('id'))->delete();
        }

        // C) Create genuinely new ones
        foreach ($incoming as $slot) {
            if (!isset($existingByKey[$slot['key']])) {
                $service->slots()->create([
                    'date'         => $slot['date'],
                    'time'         => $slot['time'],   // HH:MM
                    'is_available' => true,
                    'reguser_id'   => null,
                ]);
            }
        }
        // D) Do not touch overlapping slots → preserves booked/unavailable states
    });

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
