<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Report;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminPanelController extends Controller
{
    /**
     * Main Admin Panel index
     * Shows Users or Reports depending on tab query (?tab=users or ?tab=reports)
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'users'); // default tab
        $search = trim($request->query('search', ''));
        $sort = $request->query('sort', 'id_desc');

        // Handle Users tab
        if ($tab === 'users') {
            $roles = (array) $request->input('roles', []);
            $allowedRoles = ['admin', 'reguser', 'provider'];
            $roles = array_values(array_intersect($roles, $allowedRoles));

            $query = User::query()
                ->when($search, fn($q) => $q->where(fn($qq) =>
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%")
                ))
                ->when($roles, fn($q) => $q->whereIn('role', $roles));

            // Sorting
            switch ($sort) {
                case 'name_asc':  $query->orderBy('name', 'asc'); break;
                case 'name_desc': $query->orderBy('name', 'desc'); break;
                case 'id_asc':    $query->orderBy('id', 'asc'); break;
                default:          $query->orderBy('id', 'desc'); break;
            }

            $users = $query->paginate(12)->withQueryString();
            $users->getCollection()->transform(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'created_at' => $u->created_at,
            ]);

            return Inertia::render('AdminPanel', [
                'tab' => 'users',
                'users' => $users,
                'filters' => [
                    'search' => $search,
                    'sort' => $sort,
                    'roles' => $roles,
                ],
                'auth' => ['user' => $request->user()],
            ]);
        }

        // Handle Reports tab
        $query = Report::with(['user', 'review', 'service'])
            ->when($search, function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('service', fn($s) => $s->where('title', 'like', "%{$search}%"))
                  ->orWhereHas('review', fn($r) => $r->where('comment', 'like', "%{$search}%"));
            });

        [$col, $dir] = match ($sort) {
            'id_asc' => ['id', 'asc'],
            'id_desc' => ['id', 'desc'],
            default => ['id', 'desc'],
        };

        $query->orderBy($col, $dir);
        $reports = $query->paginate(10)->withQueryString();

        return Inertia::render('AdminPanel', [
            'tab' => 'reports',
            'reports' => $reports,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
            ],
            'auth' => ['user' => $request->user()],
        ]);
    }

    /** Delete user */
    public function destroyUser(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return back()->with('success', 'User deleted.');
    }

    /** Delete report */
    public function destroyReport(Report $report)
    {
        $report->delete();
        return back()->with('success', 'Report deleted.');
    }
}
