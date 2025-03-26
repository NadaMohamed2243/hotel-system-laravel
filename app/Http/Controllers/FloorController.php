<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class FloorController extends Controller
{
    public function index()
    {
        try {
            if (!auth()->user()->hasPermissionTo('manage floors')) {
                return abort(403, 'You do not have permission to view floors.');
            }
        } catch (\Exception $e) {
            Log::warning('Permission check failed: ' . $e->getMessage());
            if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'manager') {
                return abort(403, 'Unauthorized action.');
            }
        }

        // Fetch floors with relationships
        $query = Floor::with('manager');

        if (Auth::user()->role !== 'admin') {
            $query->where('manager_id', Auth::id());
        }

        $floors = $query->get()->map(function ($floor) {
            return [
                'id' => $floor->id,
                'name' => $floor->name,
                'number' => $floor->number,
                'created_at' => $floor->created_at->format('Y-m-d H:i:s'),
                'manager_name' => $floor->manager->name,
                'manager_email' => $floor->manager->email,
                'can_edit' => Auth::user()->role === 'admin' || $floor->manager_id === Auth::id(),
                'can_delete' => Auth::user()->role === 'admin' || $floor->manager_id === Auth::id(),
            ];
        });

        $managers = User::where('role', 'manager')
            ->select('id', 'name', 'email')
            ->get();

        return Inertia::render('Floors/Index', [
            'floors' => $floors,
            'managers' => $managers,
            'canCreate' => auth()->user()->can('create', Floor::class),
        ]);
    }

    public function create()
    {
        $managers = User::where('role', 'manager')
            ->select('id', 'name', 'email')
            ->get();

        return Inertia::render('Floors/Create', [
            'managers' => $managers
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'manager_id' => 'sometimes|exists:users,id'
        ]);

        // Set manager_id to current user if not admin
        if (Auth::user()->role !== 'admin') {
            $validated['manager_id'] = Auth::id();
        }

        $floor = Floor::create($validated);

        return Inertia::render('Floors/Index', [
            'floors' => Floor::all()
        ]);
    }

    public function show(Floor $floor)
    {
        return Inertia::render('Floors/Show', [
            'floor' => $floor->load('manager'),
        ]);
    }

    public function edit(Floor $floor)
{
    if (Auth::user()->role !== 'admin' && $floor->manager_id !== Auth::id()) {
        return redirect()->back()->with('error', 'You are not authorized to edit this floor.');
    }

    return Inertia::render('Floors/Edit', [
        'floor' => $floor->load('manager'),
        'managers' => User::where('role', 'manager')->get(['id', 'name', 'email'])
    ]);
}

    public function update(Request $request, Floor $floor)
{
    if (Auth::user()->role !== 'admin' && $floor->manager_id !== Auth::id()) {
        return redirect()->back()->with('error', 'You are not authorized to update this floor.');
    }

    $validated = $request->validate([
        'name' => 'required|string|min:3|max:255',
        'manager_id' => 'sometimes|exists:users,id'
    ]);

    if (Auth::user()->role !== 'admin') {
        unset($validated['manager_id']);
    }

    $floor->update($validated);

    // Use the correct route name (assuming admin prefix)
    return redirect()->route('admin.floors.index')
        ->with('success', 'Floor updated successfully');
}


    public function destroy(Floor $floor)
    {
        if (Auth::user()->role !== 'admin' && $floor->manager_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You are not authorized to delete this floor.');
        }

        if ($floor->rooms()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete floor with existing rooms.');
        }

        $floor->delete();

        return redirect()->route('floors.index')
            ->with('success', 'Floor deleted successfully');
    }
}
