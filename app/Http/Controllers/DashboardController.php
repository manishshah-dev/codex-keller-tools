<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    // Middleware is now applied in the routes file

    /**
     * Display the dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $user = Auth::user();
        $projects = Project::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $activeProjects = Project::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();

        $completedProjects = Project::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        return view('dashboard', [
            'projects' => $projects,
            'activeProjects' => $activeProjects,
            'completedProjects' => $completedProjects,
        ]);
    }
}

