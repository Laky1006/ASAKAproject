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
     * Shows Users or Reports depending on ?tab=users or ?tab=reports
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'users');
        $filters = $request->only(['search', 'sort', 'roles', 'type']); // ✅ include 'type' in filters

        if ($tab === 'users') {
            $query = User::query();

            // 🔍 Search by name or email
            if ($search = $request->query('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // 🎭 Role filter (multi-select)
            if ($roles = $request->query('roles')) {
                $query->whereIn('role', (array)$roles);
            }

            // ↕️ Sorting
            switch ($request->query('sort', 'id_desc')) {
                case 'name_asc': $query->orderBy('name', 'asc'); break;
                case 'name_desc': $query->orderBy('name', 'desc'); break;
                case 'id_asc': $query->orderBy('id', 'asc'); break;
                default: $query->orderBy('id', 'desc'); break;
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
                'filters' => $filters,
                'auth' => ['user' => $request->user()],
            ]);
        }

        // --- REPORTS TAB ---
        $query = Report::with(['user', 'review', 'service']);


        // 🔍 Search filter (by reason, user name, service title, review comment, or ID)
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                ->orWhereHas('service', fn($s) => $s->where('title', 'like', "%{$search}%"))
                ->orWhereHas('review', fn($r) => $r->where('comment', 'like', "%{$search}%"))
                ->orWhere('id', 'like', "%{$search}%");
            });
        }


        // 🎯 Filter by report type (service / comment)
        if ($type = $request->query('type')) {
            if ($type === 'service') {
                $query->whereNotNull('service_id');
            } elseif ($type === 'comment') {
                $query->whereNotNull('review_id');
            }
        }

        // ↕️ Sort options
        switch ($request->query('sort', 'id_desc')) {
            case 'id_asc':
                $query->orderBy('id', 'asc');
                break;
            case 'created_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'created_desc':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('id', 'desc');
                break;
        }

        // 🔄 Pagination and transformation
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
            // 🆕 Computed type for UI
            'type' => $r->service ? 'service' : ($r->review ? 'comment' : 'unknown'),
        ]);

        return Inertia::render('AdminPanel', [
            'tab' => 'reports',
            'reports' => $reports,
            'users' => null,
            'filters' => $filters,
            'auth' => ['user' => $request->user()],
        ]);
    }

    /**
     * 🗑 Delete a specific user
     */
    public function destroyUser(User $user, Request $request)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return back()->with('success', "User '{$user->name}' deleted successfully.");
    }

    /**
     * 🗑 Delete a specific report
     */
    public function destroyReport(Report $report)
    {
        $report->delete();

        return back()->with('success', "Report #{$report->id} deleted successfully.");
    }
}
