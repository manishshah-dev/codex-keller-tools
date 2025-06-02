<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\WorkableSetting;
use App\Services\WorkableService; // To mock
use Mockery\MockInterface;

class WorkableImportTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create();
        $this->project = Project::factory()->create(['user_id' => $this->adminUser->id]);

        WorkableSetting::factory()->create([
            'name' => 'Default Test Workable',
            'subdomain' => 'test-subdomain',
            'api_token' => 'test-token',
            'is_active' => true,
            'is_default' => true,
        ]);
    }

    public function test_project_candidates_page_loads_workable_candidates_list(): void
    {
        $this->mock(WorkableService::class, function (MockInterface $mock) {
            $mock->shouldReceive('listCandidates')
                ->once()
                ->andReturn([
                    ['id' => 'w123', 'name' => 'Workable Candidate 1', 'job' => ['title' => 'Developer']],
                    ['id' => 'w456', 'name' => 'Workable Candidate 2', 'job' => ['title' => 'Designer']],
                ]);
        });

        $response = $this->actingAs($this->adminUser)
                         ->get(route('projects.candidates.index', $this->project));

        $response->assertOk();
        $response->assertViewHas('workableCandidates', function ($candidates) {
            return is_array($candidates) && count($candidates) === 2 && isset($candidates[0]['name']) && $candidates[0]['name'] === 'Workable Candidate 1';
        });
        $response->assertSeeText('Workable Candidate 1 - Developer');
    }

    public function test_import_from_workable_successfully_imports_candidates(): void
    {
        $workableCandidateId = 'wk_candidate_123';
        $workableCandidateData = [
            'candidate' => [
                'id' => $workableCandidateId,
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '1234567890',
                'address' => '123 Main St, Anytown',
                'job' => ['title' => 'Software Engineer']
            ]
        ];

        $this->mock(WorkableService::class, function (MockInterface $mock) use ($workableCandidateId, $workableCandidateData) {
            $mock->shouldReceive('getCandidate')
                ->withArgs(function (WorkableSetting $setting, string $id) use ($workableCandidateId) {
                    return $id === $workableCandidateId;
                })
                ->once()
                ->andReturn($workableCandidateData);
        });

        $response = $this->actingAs($this->adminUser)
            ->post(route('projects.candidates.import-workable', $this->project), [
                'workable_candidates' => [$workableCandidateId]
            ]);

        $response->assertRedirect(route('projects.candidates.index', $this->project));
        $response->assertSessionHas('success', 'Imported 1 candidate(s).');
        $this->assertDatabaseHas('candidates', [
            'project_id' => $this->project->id,
            'workable_id' => $workableCandidateId,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'location' => '123 Main St, Anytown',
            'current_position' => 'Software Engineer',
            'source' => 'workable',
        ]);
    }

    public function test_import_from_workable_does_not_create_duplicates(): void
    {
        $workableCandidateId = 'wk_candidate_789';
        $existingCandidate = \App\Models\Candidate::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $this->adminUser->id,
            'workable_id' => $workableCandidateId,
            'first_name' => 'Existing',
            'last_name' => 'User',
            'email' => 'existing@example.com',
            'source' => 'workable',
        ]);

        $workableCandidateData = ['candidate' => [
            'id' => $workableCandidateId,
            'name' => 'Jane Roe',
            'email' => 'jane.roe@example.com',
            'job' => ['title' => 'QA Engineer']
        ]];

        $this->mock(WorkableService::class, function (MockInterface $mock) use ($workableCandidateId, $workableCandidateData) {
            $mock->shouldReceive('getCandidate')->once()->andReturn($workableCandidateData);
        });

        $initialCount = \App\Models\Candidate::count();

        $response = $this->actingAs($this->adminUser)
            ->post(route('projects.candidates.import-workable', $this->project), [
                'workable_candidates' => [$workableCandidateId]
            ]);

        $response->assertSessionHas('success', 'Imported 1 candidate(s).');
        $this->assertDatabaseCount('candidates', $initialCount);
        $this->assertDatabaseHas('candidates', [
            'id' => $existingCandidate->id,
            'workable_id' => $workableCandidateId,
            'first_name' => 'Existing',
            'email' => 'existing@example.com',
        ]);
    }

    public function test_import_fails_if_no_active_workable_setting(): void
    {
        WorkableSetting::query()->update(['is_active' => false]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('projects.candidates.import-workable', $this->project), [
                'workable_candidates' => ['some_id']
            ]);

        $response->assertRedirect(route('projects.candidates.index', $this->project));
        $response->assertSessionHas('error', 'No active Workable settings found.');
        $this->assertDatabaseMissing('candidates', ['workable_id' => 'some_id', 'project_id' => $this->project->id]);
    }

    public function test_import_handles_workable_service_exception_during_get_candidate(): void
    {
        $workableCandidateId = 'wk_ex_123';
        $this->mock(WorkableService::class, function (MockInterface $mock) use ($workableCandidateId) {
            $mock->shouldReceive('getCandidate')
                ->withArgs(function (WorkableSetting $setting, string $id) use ($workableCandidateId) {
                    return $id === $workableCandidateId;
                })
                ->once()
                ->andThrow(new \Exception('Workable API is down'));
        });

        $response = $this->actingAs($this->adminUser)
            ->post(route('projects.candidates.import-workable', $this->project), [
                'workable_candidates' => [$workableCandidateId]
            ]);
        $response->assertSessionHas('warning', 'Imported 0 candidate(s). 1 failed.');
        $this->assertDatabaseMissing('candidates', ['workable_id' => $workableCandidateId, 'project_id' => $this->project->id]);

    }

    public function test_import_validation_fails_for_missing_candidate_ids_input(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('projects.candidates.import-workable', $this->project), [
                 // 'workable_candidates' key is completely missing
            ]);

        $response->assertSessionHasErrors('workable_candidates');
    }

    public function test_import_validation_fails_for_empty_candidate_ids_array(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('projects.candidates.import-workable', $this->project), [
                'workable_candidates' => [] // Empty array
            ]);

        $response->assertSessionHasErrors('workable_candidates');
    }
}
