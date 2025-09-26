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
        $search = (string) $request->query('search', '');
        $sort   = (string) $request->query('sort', 'name_asc');
        $roles  = (array)  $request->input('roles', []);

        // sanitize inputs
        $allowedSorts = ['name_asc', 'name_desc', 'id_asc', 'id_desc'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'name_asc';
        }

        $allowedRoles = ['admin', 'reguser', 'provider'];
        $roles = array_values(array_intersect($roles, $allowedRoles));

        $query = User::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(!empty($roles), fn ($q) => $q->whereIn('role', $roles));

        // sorting
        switch ($sort) {
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'id_asc':
                $query->orderBy('id', 'asc');
                break;
            case 'id_desc':
                $query->orderBy('id', 'desc');
                break;
            case 'name_asc':
            default:
                $query->orderBy('name', 'asc');
                break;
        }

        $users = $query
            ->paginate(12)
            ->appends([
                'search' => $search ?: null,
                'sort'   => $sort ?: null,
                'roles'  => !empty($roles) ? $roles : null,
            ]);

        // expose safe fields
        $users->getCollection()->transform(fn ($u) => [
            'id'         => $u->id,
            'name'       => $u->name,
            'email'      => $u->email,
            'role'       => $u->role ?? null,
            'created_at' => $u->created_at,
        ]);

        return Inertia::render('Admin', [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'sort'   => $sort,
                'roles'  => $roles,
            ],
        ]);
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return back()->with('success', 'User deleted.');
    }

}
