<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    // ✅ User-side reporting
    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
            'review_id' => 'nullable|exists:reviews,id',
            'service_id' => 'nullable|exists:services,id',
        ]);

        if (!$request->review_id && !$request->service_id) {
            return response()->json(['error' => 'You must report either a review or a service.'], 422);
        }

        $report = Report::create([
            'user_id'   => auth()->id(),
            'review_id' => $request->review_id,
            'service_id'=> $request->service_id,
            'reason'    => $request->reason,
        ]);

        return response()->json(['message' => 'Report submitted successfully', 'report' => $report], 201);
    }

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

        return Inertia::render('Admin/Reports', [
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

