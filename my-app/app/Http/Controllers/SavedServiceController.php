<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SavedService;
use App\Models\Service;
use Inertia\Inertia;

class SavedServiceController extends Controller
{
    // Show all saved services
    public function index()
        {
            $user = auth()->user();
            
            // Get saved services grouped by folder
            $savedServices = SavedService::where('user_id', $user->id)
                ->with('service.provider.user')
                ->orderBy('folder_name')
                ->orderBy('created_at', 'desc')
                ->get();

            // Group by folder_name and get folder names
            $groupedServices = $savedServices->groupBy('folder_name');
            $folderNames = $savedServices->pluck('folder_name')->filter()->unique()->values();

            // Add any folders from session (newly created empty folders)
            $newFolders = session('new_folders', []);
            foreach ($newFolders as $folderName) {
                if (!$folderNames->contains($folderName)) {
                    $folderNames->push($folderName);
                    $groupedServices[$folderName] = collect(); // Empty collection
                }
            }

            return Inertia::render('Services/SavedServices', [
                'groupedServices' => $groupedServices,
                'folderNames' => $folderNames,
            ]);
        }


    // Save a service
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'folder_name' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();

        $existing = SavedService::where('user_id', $user->id)
            ->where('service_id', $validated['service_id'])
            ->first();

        if ($existing) {
            return back()->withErrors(['error' => 'Service already saved.']);
        }

        SavedService::create([
            'user_id' => $user->id,
            'service_id' => $validated['service_id'],
            'folder_name' => $validated['folder_name'],
        ]);

        return back()->with('success', 'Service saved successfully!');
    }

    // Remove a saved service
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
        ]);

        $user = auth()->user();

        SavedService::where('user_id', $user->id)
            ->where('service_id', $validated['service_id'])
            ->delete();

        return back()->with('success', 'Service removed from saved.');
    }

    // Create a new folder
    public function createFolder(Request $request)
    {
        $validated = $request->validate([
            'folder_name' => 'required|string|max:255',
        ]);

        $user = auth()->user();
        $folderName = trim($validated['folder_name']);

        // Check if folder already exists
        $exists = SavedService::where('user_id', $user->id)
            ->where('folder_name', $folderName)
            ->exists();

        if ($exists) {
            return back()->withErrors(['error' => 'Folder already exists.']);
        }

        // Store new folder in session temporarily
        $newFolders = session('new_folders', []);
        $newFolders[] = $folderName;
        session(['new_folders' => $newFolders]);

        return back()->with('success', 'Folder "' . $folderName . '" created successfully!');
    }


// Rename a folder
public function renameFolder(Request $request)
{
    $validated = $request->validate([
        'old_name' => 'required|string|max:255',
        'new_name' => 'required|string|max:255',
    ]);

    $user = auth()->user();
    $oldName = trim($validated['old_name']);
    $newName = trim($validated['new_name']);

    if ($oldName === $newName) {
        return back()->withErrors(['error' => 'New name must be different.']);
    }

    // Check if new name already exists
    $exists = SavedService::where('user_id', $user->id)
        ->where('folder_name', $newName)
        ->exists();

    if ($exists) {
        return back()->withErrors(['error' => 'A folder with that name already exists.']);
    }

    // Update all services in the folder
    $updated = SavedService::where('user_id', $user->id)
        ->where('folder_name', $oldName)
        ->update(['folder_name' => $newName]);

    // Also update session folders if they exist
    $newFolders = session('new_folders', []);
    if (($key = array_search($oldName, $newFolders)) !== false) {
        $newFolders[$key] = $newName;
        session(['new_folders' => $newFolders]);
    }

    return redirect()->route('saved-services.index')->with('success', 'Folder renamed successfully.');
}

// Delete a folder (moves services to unsorted)
public function deleteFolder(Request $request)
{
    $validated = $request->validate([
        'folder_name' => 'required|string|max:255',
    ]);

    $user = auth()->user();
    $folderName = trim($validated['folder_name']);

    // Move all services in this folder to unsorted (null folder_name)
    SavedService::where('user_id', $user->id)
        ->where('folder_name', $folderName)
        ->update(['folder_name' => null]);

    // Remove from session folders if it exists
    $newFolders = session('new_folders', []);
    if (($key = array_search($folderName, $newFolders)) !== false) {
        unset($newFolders[$key]);
        session(['new_folders' => array_values($newFolders)]);
    }

    return redirect()->route('saved-services.index')->with('success', 'Folder deleted. Services moved to unsorted.');
}


    // Move service to different folder
    public function moveToFolder(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'folder_name' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();

        $savedService = SavedService::where('user_id', $user->id)
            ->where('service_id', $validated['service_id'])
            ->first();

        if (!$savedService) {
            return back()->withErrors(['error' => 'Service not found in saved services.']);
        }

        $savedService->update(['folder_name' => $validated['folder_name']]);

        $folderText = $validated['folder_name'] ? '"' . $validated['folder_name'] . '"' : 'unsorted';
        return back()->with('success', 'Service moved to ' . $folderText . '.');
    }
}
