<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminReportController extends Controller
{
    // ✅ Admin index page
    public function index(Request $request)
    {
        $query = Report::with(['user', 'review', 'service']);

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('reason', 'like', "%{$search}%")
                  ->orWhereHas('service', fn($q) => $q->where('title', 'like', "%{$search}%"))
                  ->orWhereHas('review', fn($q) => $q->where('comment', 'like', "%{$search}%"))
                  ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        // Sorting
        $sort = $request->input('sort', 'id_desc');
        [$col, $dir] = match ($sort) {
            'id_asc'   => ['id', 'asc'],
            'id_desc'  => ['id', 'desc'],
            default    => ['id', 'desc'],
        };
        $query->orderBy($col, $dir);

        $reports = $query->paginate(10)->withQueryString();

        return Inertia::render('AdminPanel', [
            'reports' => $reports,
            'filters' => $request->only('search', 'sort'),
            'auth'    => ['user' => $request->user()],
        ]);
    }

    // ✅ Admin delete
    public function destroy(Report $report)
    {
        $report->delete();
        return back()->with('success', 'Report deleted.');
    }
}
