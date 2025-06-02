<?php

use App\Http\Controllers\AiQuestionController;
use App\Http\Controllers\AISettingController;
use App\Http\Controllers\CompanyResearchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IntakeFormController;
use App\Http\Controllers\JobDescriptionController;
use App\Http\Controllers\JobDescriptionTemplateController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectRequirementController;
use App\Http\Controllers\QualifyingQuestionController;
use App\Http\Controllers\SalaryComparisonController;
use App\Http\Controllers\SearchStringController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\CandidateProfileController;
use App\Http\Controllers\TrashController;
use App\Http\Controllers\CandidateProfileSubmissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkableSettingController;
use App\Http\Controllers\WorkableJobController;
use Illuminate\Support\Facades\Route;

Route::view('/inactive', 'auth.inactive')->name('inactive');


Route::middleware(['auth', 'verified', 'active'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Projects
    Route::resource('projects', ProjectController::class);
    
    // Intake Forms
    Route::get('/projects/{project}/intake-form', [IntakeFormController::class, 'show'])->name('projects.intake_forms.show');
    Route::get('/projects/{project}/intake-form/create', [IntakeFormController::class, 'create'])->name('projects.intake_forms.create');
    Route::post('/projects/{project}/intake-form', [IntakeFormController::class, 'store'])->name('projects.intake_forms.store');
    Route::get('/projects/{project}/intake-form/edit', [IntakeFormController::class, 'edit'])->name('projects.intake_forms.edit');
    Route::put('/projects/{project}/intake-form', [IntakeFormController::class, 'update'])->name('projects.intake_forms.update');
    Route::delete('/projects/{project}/intake-form', [IntakeFormController::class, 'destroy'])->name('projects.intake_forms.destroy');
    
    // Job Descriptions (Project-specific routes)
    Route::get('/projects/{project}/job-descriptions', [JobDescriptionController::class, 'projectIndex'])->name('projects.job_descriptions.index');
    Route::get('/projects/{project}/job-descriptions/{jobDescription?}', [JobDescriptionController::class, 'projectShow'])->name('projects.job_descriptions.show');
    
    // Job Descriptions (Standalone routes)
    Route::get('/job-descriptions', [JobDescriptionController::class, 'index'])->name('job-descriptions.index');
    Route::get('/job-descriptions/create', [JobDescriptionController::class, 'create'])->name('job-descriptions.create');
    Route::post('/job-descriptions', [JobDescriptionController::class, 'store'])->name('job-descriptions.store');
    Route::get('/job-descriptions/{jobDescription}', [JobDescriptionController::class, 'show'])->name('job-descriptions.show');
    Route::get('/job-descriptions/{jobDescription}/edit', [JobDescriptionController::class, 'edit'])->name('job-descriptions.edit');
    Route::put('/job-descriptions/{jobDescription}', [JobDescriptionController::class, 'update'])->name('job-descriptions.update');
    Route::delete('/job-descriptions/{jobDescription}', [JobDescriptionController::class, 'destroy'])->name('job-descriptions.destroy');
    Route::post('/job-descriptions/generate', [JobDescriptionController::class, 'generate'])->name('job-descriptions.generate');
    Route::post('/job-descriptions/{jobDescription}/version', [JobDescriptionController::class, 'createVersion'])->name('job-descriptions.create_version');
    Route::get('/job-descriptions/{jobDescription}/export', [JobDescriptionController::class, 'export'])->name('job-descriptions.export');
    
    // Qualifying Questions
    Route::resource('job-descriptions.qualifying-questions', QualifyingQuestionController::class)->except(['index', 'show']);
    
    // Job Description Templates
    Route::resource('job-description-templates', JobDescriptionTemplateController::class);
    Route::post('/job-description-templates/{template}/duplicate', [JobDescriptionTemplateController::class, 'duplicate'])->name('job-description-templates.duplicate');
    
    // Candidate Routes (nested under projects)
    Route::resource('projects.candidates', CandidateController::class)->shallow();
    // Add specific routes for analysis if needed, e.g.:
    Route::post('/candidates/{candidate}/analyze', [CandidateController::class, 'analyze'])->name('candidates.analyze');
    Route::post('/projects/{project}/candidates/analyze-all', [CandidateController::class, 'analyzeAll'])->name('projects.candidates.analyzeAll'); // Add route for batch analysis
    Route::get('/candidates/{candidate}/resume/view', [CandidateController::class, 'viewResume'])->name('candidates.resume.view'); // Route for viewing resume
    Route::get('/candidates/{candidate}/resume/download', [CandidateController::class, 'downloadResume'])->name('candidates.resume.download'); // Route for downloading resume
    
    // Company Research
    Route::get('/projects/{project}/company-research', [CompanyResearchController::class, 'show'])->name('projects.company_research.show');
    Route::get('/projects/{project}/company-research/create', [CompanyResearchController::class, 'create'])->name('projects.company_research.create');
    Route::post('/projects/{project}/company-research', [CompanyResearchController::class, 'store'])->name('projects.company_research.store');
    Route::get('/projects/{project}/company-research/edit', [CompanyResearchController::class, 'edit'])->name('projects.company_research.edit');
    Route::put('/projects/{project}/company-research', [CompanyResearchController::class, 'update'])->name('projects.company_research.update');
    Route::delete('/projects/{project}/company-research', [CompanyResearchController::class, 'destroy'])->name('projects.company_research.destroy');
    
    // Salary Comparisons
    Route::resource('projects.salary_comparisons', SalaryComparisonController::class)->shallow();
    
    // AI Questions
    Route::resource('projects.ai_questions', AiQuestionController::class)->shallow();
    
    // Search Strings
    Route::resource('projects.search_strings', SearchStringController::class)->shallow();
    
    // Candidates & CV Analyzer
    Route::get('/candidates', [CandidateController::class, 'index'])->name('candidates.index');
    Route::get('/projects/{project}/candidates', [CandidateController::class, 'projectIndex'])->name('projects.candidates.index');
    Route::get('/projects/{project}/candidates/create', [CandidateController::class, 'create'])->name('projects.candidates.create');
    Route::post('/projects/{project}/candidates', [CandidateController::class, 'store'])->name('projects.candidates.store');
    Route::get('/projects/{project}/candidates/{candidate}', [CandidateController::class, 'projectShow'])->name('projects.candidates.show');
    Route::get('/projects/{project}/candidates/{candidate}/edit', [CandidateController::class, 'edit'])->name('projects.candidates.edit');
    Route::put('/projects/{project}/candidates/{candidate}', [CandidateController::class, 'update'])->name('projects.candidates.update');
    Route::delete('/projects/{project}/candidates/{candidate}', [CandidateController::class, 'destroy'])->name('projects.candidates.destroy');
    
    // CV Analyzer
    Route::get('/projects/{project}/analyzer', [CandidateController::class, 'analyzer'])->name('projects.analyzer');
    Route::post('/projects/{project}/analyzer/chat', [CandidateController::class, 'chat'])->name('projects.analyzer.chat');
    Route::post('/projects/{project}/candidates/import-workable', [CandidateController::class, 'importFromWorkable'])->name('projects.candidates.import-workable');
    Route::post('/projects/{project}/candidates/batch-upload', [CandidateController::class, 'batchUpload'])->name('projects.candidates.batch-upload');
    
    // Project Requirements
    Route::post('/projects/{project}/requirements', [ProjectRequirementController::class, 'store'])->name('projects.requirements.store');
    Route::delete('/projects/{project}/requirements/{requirement}', [ProjectRequirementController::class, 'destroy'])->name('projects.requirements.destroy');
    
    // Candidate Profiles
    Route::get('/profiles', [CandidateProfileController::class, 'projectSelection'])->name('profiles.project-selection');
    Route::get('/projects/{project}/profiles', [CandidateProfileController::class, 'index'])->name('projects.profiles.index');
    Route::get('/projects/{project}/candidates/{candidate}/profiles/create', [CandidateProfileController::class, 'create'])->name('projects.candidates.profiles.create');
    Route::post('/projects/{project}/candidates/{candidate}/profiles', [CandidateProfileController::class, 'store'])->name('projects.candidates.profiles.store');
    Route::get('/projects/{project}/candidates/{candidate}/profiles/{profile}', [CandidateProfileController::class, 'show'])->name('projects.candidates.profiles.show');
    Route::get('/projects/{project}/candidates/{candidate}/profiles/{profile}/edit', [CandidateProfileController::class, 'edit'])->name('projects.candidates.profiles.edit');
    Route::put('/projects/{project}/candidates/{candidate}/profiles/{profile}', [CandidateProfileController::class, 'update'])->name('projects.candidates.profiles.update');
    Route::delete('/projects/{project}/candidates/{candidate}/profiles/{profile}', [CandidateProfileController::class, 'destroy'])->name('projects.candidates.profiles.destroy');
    Route::get('/projects/{project}/candidates/{candidate}/profiles/{profile}/generate', [CandidateProfileController::class, 'showGenerate'])->name('projects.candidates.profiles.show-generate');
    Route::post('/projects/{project}/candidates/{candidate}/profiles/{profile}/generate', [CandidateProfileController::class, 'generate'])->name('projects.candidates.profiles.generate');
    Route::get('/projects/{project}/candidates/{candidate}/profiles/{profile}/export', [CandidateProfileController::class, 'export'])->name('projects.candidates.profiles.export');
    
    // Candidate Profile Submissions
    Route::get('/projects/{project}/candidates/{candidate}/profiles/{profile}/create', [CandidateProfileSubmissionController::class, 'create'])->name('projects.candidates.profiles.submissions.create');
    Route::get('/mail-logs', [CandidateProfileSubmissionController::class, 'show'])->name('submissions.show');
    Route::post('/projects/{project}/candidates/{candidate}/profiles/{profile}/submissions', [CandidateProfileSubmissionController::class, 'store'])->name('projects.candidates.profiles.submissions.store');
    
    // Keywords
    Route::resource('projects.keywords', KeywordController::class)->shallow();
        
    // User Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
});

// Admin Routes
Route::middleware(['auth', 'verified', 'active', 'role:admin'])->group(function () {
    // User Management (Admin only)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
    Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');

    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');

    // Trash (Deleted Items)
    Route::get('/trash', [TrashController::class, 'index'])->name('trash.index');
    Route::post('/trash/projects/{id}/restore', [TrashController::class, 'restoreProject'])->name('trash.projects.restore');
    Route::post('/trash/job-descriptions/{id}/restore', [TrashController::class, 'restoreJobDescription'])->name('trash.job-descriptions.restore');
    Route::post('/trash/candidates/{id}/restore', [TrashController::class, 'restoreCandidate'])->name('trash.candidates.restore');
    Route::post('/trash/templates/{id}/restore', [TrashController::class, 'restoreTemplate'])->name('trash.templates.restore');
    Route::post('/trash/questions/{id}/restore', [TrashController::class, 'restoreQuestion'])->name('trash.questions.restore');
    
    // Permanent deletion routes
    Route::delete('/trash/projects/{id}', [TrashController::class, 'destroyProject'])->name('trash.projects.destroy');
    Route::delete('/trash/job-descriptions/{id}', [TrashController::class, 'destroyJobDescription'])->name('trash.job-descriptions.destroy');
    Route::delete('/trash/candidates/{id}', [TrashController::class, 'destroyCandidate'])->name('trash.candidates.destroy');
    Route::delete('/trash/templates/{id}', [TrashController::class, 'destroyTemplate'])->name('trash.templates.destroy');
    Route::delete('/trash/questions/{id}', [TrashController::class, 'destroyQuestion'])->name('trash.questions.destroy');
    
    // Bulk deletion routes
    Route::post('/trash/projects/bulk-delete', [TrashController::class, 'bulkDestroyProjects'])->name('trash.projects.bulk-destroy');
    Route::post('/trash/job-descriptions/bulk-delete', [TrashController::class, 'bulkDestroyJobDescriptions'])->name('trash.job-descriptions.bulk-destroy');
    Route::post('/trash/candidates/bulk-delete', [TrashController::class, 'bulkDestroyCandidates'])->name('trash.candidates.bulk-destroy');
    Route::post('/trash/templates/bulk-delete', [TrashController::class, 'bulkDestroyTemplates'])->name('trash.templates.bulk-destroy');
    Route::post('/trash/questions/bulk-delete', [TrashController::class, 'bulkDestroyQuestions'])->name('trash.questions.bulk-destroy');
    
    // Bulk purge route
    Route::post('/trash/purge', [TrashController::class, 'purge'])->name('trash.purge')->middleware('can:manage-ai-settings');

    // AI Settings (Admin only)
    Route::get('/ai-settings', [AISettingController::class, 'index'])->name('ai-settings.index');
    Route::get('/ai-settings/create', [AISettingController::class, 'create'])->name('ai-settings.create');
    Route::post('/ai-settings', [AISettingController::class, 'store'])->name('ai-settings.store');
    Route::get('/ai-settings/{aiSetting}/edit', [AISettingController::class, 'edit'])->name('ai-settings.edit');
    Route::put('/ai-settings/{aiSetting}', [AISettingController::class, 'update'])->name('ai-settings.update');
    Route::delete('/ai-settings/{aiSetting}', [AISettingController::class, 'destroy'])->name('ai-settings.destroy');
    Route::post('/ai-settings/{aiSetting}/test-connection', [AISettingController::class, 'testConnection'])->name('ai-settings.test-connection');

    // AI Prompts
    Route::get('/ai-settings/prompts', [AISettingController::class, 'prompts'])->name('ai-settings.prompts');
    Route::get('/ai-settings/prompts/create', [AISettingController::class, 'createPrompt'])->name('ai-settings.prompts.create');
    Route::post('/ai-settings/prompts', [AISettingController::class, 'storePrompt'])->name('ai-settings.prompts.store');
    Route::get('/ai-settings/prompts/{prompt}/edit', [AISettingController::class, 'editPrompt'])->name('ai-settings.prompts.edit');
    Route::put('/ai-settings/prompts/{prompt}', [AISettingController::class, 'updatePrompt'])->name('ai-settings.prompts.update');
    Route::delete('/ai-settings/prompts/{prompt}', [AISettingController::class, 'destroyPrompt'])->name('ai-settings.prompts.destroy');

    // Workable Settings
    Route::resource('workable-settings', WorkableSettingController::class)->except(['show']);

    // Workable Jobs
    Route::get('/workable-jobs', [WorkableJobController::class, 'index'])->name('workable-jobs.index');
    Route::post('/workable-jobs/fetch', [WorkableJobController::class, 'fetch'])->name('workable-jobs.fetch');
    Route::post('/workable-jobs/import-candidates', [WorkableJobController::class, 'importCandidates'])->name('workable-jobs.import-candidates');

});

require __DIR__.'/auth.php';
