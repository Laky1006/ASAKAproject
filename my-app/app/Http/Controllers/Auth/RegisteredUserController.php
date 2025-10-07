<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Reguser;
use App\Models\Provider;
use App\Http\Requests\Auth\RegisterRequest;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */public function store(RegisterRequest $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();

        $user = \App\Models\User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        event(new \Illuminate\Auth\Events\Registered($user));

        if ($data['role'] === 'reguser') {
            \App\Models\Reguser::create(['user_id' => $user->id]);
        } elseif ($data['role'] === 'provider') {
            \App\Models\Provider::create(['user_id' => $user->id]);
        }

        \Illuminate\Support\Facades\Auth::login($user);

        return redirect()->route('verification.notice');

    }
}

