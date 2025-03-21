<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ManagerController extends Controller
{
    public function index()
    {
        $managers = User::where('role', 'manager')->get();
        return Inertia::render('Managers/Index', ['managers' => $managers]);
    }
}
