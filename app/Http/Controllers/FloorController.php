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

    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);

        $floorsQuery = Floor::with('manager');
            
        
        $paginatedFloors = $floorsQuery->paginate($pageSize);
        
        $floors = $paginatedFloors->map(function ($floor) {
            $isOwnFloor = $floor->manager_id === Auth::id();

            return [
                'id' => $floor->id,
                'name' => $floor->name,
                'number' => $floor->number,
                'created_at' => $floor->created_at->format('Y-m-d H:i:s'),
                'manager_name' => $floor->manager->name,
                'manager_email' => $floor->manager->email,
                'manager_id' => $floor->manager_id,
                'can_edit' => Auth::user()->role === 'admin' || $isOwnFloor,
                'can_delete' => Auth::user()->role === 'admin' || $isOwnFloor,
                'is_own_floor' => $isOwnFloor,
            ];
        });

        return Inertia::render('Floors/Index', [
            'floors' => [
                'data' => $floors,
                'meta' => [
                    'total' => $paginatedFloors->total(),
                    'pageCount' => $paginatedFloors->lastPage(),
                    'pageSize' => $paginatedFloors->perPage(),
                    'pageIndex' => $paginatedFloors->currentPage() - 1, // 0-based for TanStack Table
                    'from' => $paginatedFloors->firstItem(),
                    'to' => $paginatedFloors->lastItem(),
                ]
            ],
            'managers' => User::where('role', 'manager')->get(['id', 'name', 'email']),
            'canCreate' => true,
            'is_manager_view' => Auth::user()->role === 'manager',
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

        if (Auth::user()->role !== 'admin') {
            $validated['manager_id'] = Auth::id();
        }

        $floor = Floor::create($validated);
        $floor->load('manager');

        return redirect()->back()
            ->with('success', 'Floor created successfully')
            ->with('floor', [
                'id' => $floor->id,
                'name' => $floor->name,
                'number' => $floor->number,
                'manager_id' => $floor->manager_id,
                'manager_name' => $floor->manager->name,
                'created_at' => $floor->created_at->format('Y-m-d H:i:s'),
                'can_edit' => true,
                'can_delete' => true,
                'is_own_floor' => true
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
        if (Auth::user()->role === 'manager' && $floor->manager_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You are not authorized to edit this floor.');
        }

        return Inertia::render('Floors/Edit', [
            'floor' => $floor->load('manager'),
            'managers' => User::where('role', 'manager')->get(['id', 'name', 'email'])
        ]);
    }


    public function update(Request $request, Floor $floor)
    {
        if (Auth::user()->role === 'manager' && $floor->manager_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You are not authorized to update this floor.');
        }

        $validated = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'manager_id' => 'sometimes|exists:users,id'
        ]);

        // Managers cannot change the floor manager
        if (Auth::user()->role !== 'admin') {
            unset($validated['manager_id']);
        }

        $floor->update($validated);
        $floor->refresh();
        $floor->load('manager');

        return redirect()->back()
            ->with('success', 'Floor updated successfully')
            ->with('floor', [
                'id' => $floor->id,
                'name' => $floor->name,
                'number' => $floor->number,
                'manager_id' => $floor->manager_id,
                'manager_name' => $floor->manager->name,
                'created_at' => $floor->created_at->format('Y-m-d H:i:s'),
                'can_edit' => true,
                'can_delete' => true,
                'is_own_floor' => true
            ]);
    }

    public function destroy(Floor $floor)
    {
       
        if ($floor->rooms()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete floor with existing rooms.');
        }

        $floor->delete();

        $routeName = Auth::user()->role === 'admin' ? 'admin.floors.index' : 'manager.floors.index';

        return redirect()->route($routeName)
            ->with('success', 'Floor deleted successfully');
    }
}
