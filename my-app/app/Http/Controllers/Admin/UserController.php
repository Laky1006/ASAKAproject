<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->toString();

        $users = User::query()
            ->when($search !== '', fn($q) =>
                $q->where('name','like',"%{$search}%")->orWhere('email','like',"%{$search}%")
            )
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        // expose safe fields
        $users->getCollection()->transform(fn($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->role ?? null,
            'created_at' => $u->created_at,
        ]);

        // IMPORTANT: render Admin (same component you tested)
        return Inertia::render('Admin', [
            'users'   => $users,
            'filters' => ['search' => $search],
        ]);
    }
}
