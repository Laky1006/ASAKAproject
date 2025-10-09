<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProviderController extends Controller
{
    public function index()
    {
        $providers = Provider::with(['user:id,name', 'services:id,provider_id,title,price,rating'])->get();

        return Inertia::render('Providers/Index', [
            'providers' => $providers,
            'auth'      => ['user' => Auth::user()],
        ]);
    }

    public function show($id)
{
    $provider = \App\Models\Provider::with([
        'user:id,name,email',
        'services' => function ($q) {
            $q->select('id','provider_id','title','price','rating','banner','description','labels')
              ->with(['provider.user:id,name']); // <-- needed for card: service.provider.user.name
        },
    ])->findOrFail($id);

    return \Inertia\Inertia::render('Providers/Show', [
        'provider' => $provider,
        'auth'     => ['user' => \Illuminate\Support\Facades\Auth::user()],
    ]);
}


}
