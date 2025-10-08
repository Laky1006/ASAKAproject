<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Report;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminPanelController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'users');
        $filters = $request->only(['search', 'sort', 'roles', 'type']);

        /* ---------------- USERS TAB ---------------- */
        if ($tab === 'users') {
            $query = User::query();

            // 🔍 Search
            if ($search = $request->query('search')) {
                $query->where(fn($q) =>
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                );
            }

            // 🎭 Filter by role
            if ($roles = $request->query('roles')) {
                $query->whereIn('role', (array)$roles);
            }

            // ↕️ Sorting
            switch ($request->query('sort', 'id_desc')) {
                case 'name_asc':  $query->orderBy('name', 'asc'); break;
                case 'name_desc': $query->orderBy('name', 'desc'); break;
                case 'id_asc':    $query->orderBy('id', 'asc'); break;
                default:          $query->orderBy('id', 'desc'); break;
            }

            $users = $query->paginate(12)->withQueryString()->through(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'created_at' => $u->created_at,
            ]);

            return Inertia::render('AdminPanel', [
                'tab' => 'users',
                'users' => $users,
                'reports' => null,
                'services' => null,
                'filters' => $filters,
                'auth' => ['user' => $request->user()],
            ]);
        }

        /* ---------------- REPORTS TAB ---------------- */
        if ($tab === 'reports') {
            $query = Report::with(['user', 'review', 'service']);

            // 🔍 Search
            if ($search = $request->query('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('reason', 'like', "%{$search}%")
                        ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('service', fn($s) => $s->where('title', 'like', "%{$search}%"))
                        ->orWhereHas('review', fn($r) => $r->where('comment', 'like', "%{$search}%"))
                        ->orWhere('id', 'like', "%{$search}%");
                });
            }

            // 🎯 Filter by type
            if ($type = $request->query('type')) {
                if ($type === 'service') $query->whereNotNull('service_id');
                elseif ($type === 'comment') $query->whereNotNull('review_id');
            }

            // ↕️ Sort
            switch ($request->query('sort', 'id_desc')) {
                case 'id_asc':        $query->orderBy('id', 'asc'); break;
                case 'created_asc':   $query->orderBy('created_at', 'asc'); break;
                case 'created_desc':  $query->orderBy('created_at', 'desc'); break;
                default:              $query->orderBy('id', 'desc'); break;
            }

            $reports = $query->paginate(10)->withQueryString()->through(fn($r) => [
                'id' => $r->id,
                'reason' => $r->reason,
                'created_at' => $r->created_at,
                'user' => [
                    'id' => $r->user->id,
                    'name' => $r->user->name,
                ],
                'service' => $r->service ? [
                    'id' => $r->service->id,
                    'title' => $r->service->title,
                ] : null,
                'review' => $r->review ? [
                    'id' => $r->review->id,
                    'service_id' => $r->review->service_id,
                    'content' => $r->review->content,
                ] : null,
                'type' => $r->service ? 'service' : ($r->review ? 'comment' : 'unknown'),
            ]);

            return Inertia::render('AdminPanel', [
                'tab' => 'reports',
                'users' => null,
                'reports' => $reports,
                'services' => null,
                'filters' => $filters,
                'auth' => ['user' => $request->user()],
            ]);
        }

        /* ---------------- SERVICES TAB ---------------- */
        if ($tab === 'services') {
            $query = Service::with('provider.user');

            // 🔍 Search by title or provider name
            if ($search = $request->query('search')) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhereHas('provider.user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            }

            // ↕️ Sorting
            switch ($request->query('sort', 'id_desc')) {
                case 'id_asc': $query->orderBy('id', 'asc'); break;
                case 'title_asc': $query->orderBy('title', 'asc'); break;
                case 'title_desc': $query->orderBy('title', 'desc'); break;
                default: $query->orderBy('id', 'desc'); break;
            }

            $services = $query->paginate(10)->withQueryString()->through(fn($s) => [
                'id' => $s->id,
                'title' => $s->title,
                'rating' => $s->rating,
                'created_at' => $s->created_at,
                'provider' => [
                    'id' => $s->provider->id ?? null,
                    'name' => $s->provider->user->name ?? 'Unknown',
                ],
            ]);

            return Inertia::render('AdminPanel', [
                'tab' => 'services',
                'services' => $services,
                'users' => null,
                'reports' => null,
                'filters' => $filters,
                'auth' => ['user' => $request->user()],
            ]);
        }

        // Default fallback
        return redirect()->route('admin-panel.dashboard', ['tab' => 'users']);
    }

    /* ---------------- DELETE METHODS ---------------- */
    public function destroyUser(User $user, Request $request)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return back()->with('success', "User '{$user->name}' deleted successfully.");
    }

    public function destroyReport(Report $report)
    {
        $report->delete();
        return back()->with('success', "Report #{$report->id} deleted successfully.");
    }

    public function destroyService(Service $service)
    {
        $service->delete();
        return back()->with('success', "Service '{$service->title}' deleted successfully.");
    }
}
