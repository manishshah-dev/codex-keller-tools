<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectRequirement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProjectRequirementController extends Controller
{
    /**
     * Store a new project requirement.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:skill,experience,education,certification,language,location,industry,tool',
            'weight' => 'required|numeric|min:0|max:1',
            'is_required' => 'required|boolean',
            'description' => 'nullable|string',
            'source' => 'nullable|string|in:manual,job_description,chat',
        ]);
        
        try {
            // Create the requirement
            $requirement = new ProjectRequirement([
                'project_id' => $project->id,
                'user_id' => Auth::id(),
                'type' => $validated['type'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'weight' => $validated['weight'],
                'is_required' => $validated['is_required'],
                'is_active' => true,
                'source' => $validated['source'] ?? 'chat',
                'created_by_chat' => $validated['source'] === 'chat',
            ]);
            
            $requirement->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Requirement saved successfully',
                'requirement' => $requirement,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save requirement: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Deactivate (remove) a project requirement.
     */
    public function destroy(Project $project, ProjectRequirement $requirement)
    {
        $this->authorize('update', $project);

        if ($requirement->project_id !== $project->id) {
            return response()->json([
                'success' => false,
                'message' => 'Requirement not found for this project',
            ], 404);
        }

        try {
            $requirement->is_active = false;
            $requirement->save();

            Log::info('Project requirement removed via CV Analyzer', [
                'project_id' => $project->id,
                'requirement_id' => $requirement->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Requirement removed successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to remove project requirement: ' . $e->getMessage(), [
                'project_id' => $project->id,
                'requirement_id' => $requirement->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove requirement: ' . $e->getMessage(),
            ], 500);
        }
    }
}