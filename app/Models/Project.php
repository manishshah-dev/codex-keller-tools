<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Basic project information
        'user_id',
        'title',
        'department',
        'location',
        'description',
        'status',
        'ai_processing_status',
        
        // Intake form fields
        'job_title',
        'required_skills',
        'preferred_skills',
        'experience_level',
        'education_requirements',
        'employment_type',
        'salary_range',
        'additional_notes',
        'claap_recording_url',
        'claap_transcript',
        
        // Company research fields
        'company_name',
        'founding_date',
        'company_size',
        'turnover',
        'linkedin_url',
        'website_url',
        'articles',
        'reviews',
        'competitors',
        'industry_details',
        'typical_clients',
        
        // Job description fields
        'overview',
        'responsibilities',
        'requirements_non_negotiable',
        'requirements_preferred',
        'compensation_range',
        'benefits',
        'disclaimer',
        'jd_file_path',
        'jd_status',
        
        // Salary comparison fields
        'average_salary',
        'min_salary',
        'max_salary',
        'similar_job_postings',
        'salary_data_source',
        
        // Search strings fields
        'linkedin_boolean_string',
        'google_xray_linkedin_string',
        'google_xray_cv_string',
        'search_string_notes',
        
        // Keywords fields
        'keywords',
        'synonyms',
        'translations',
        'translation_language',
        
        // AI Questions fields
        'candidate_questions',
        'recruiter_questions',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'founding_date' => 'date',
        'average_salary' => 'decimal:2',
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
    ];

    /**
     * Get the user that owns the project.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the job descriptions for the project.
     */
    public function jobDescriptions(): HasMany
    {
        return $this->hasMany(JobDescription::class);
    }
    
    /**
     * Get the candidates for the project.
     */
    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }
    
    /**
     * Get the requirements for the project.
     */
    public function requirements(): HasMany
    {
        return $this->hasMany(ProjectRequirement::class);
    }
    
    /**
     * Get the active requirements for the project.
     */
    public function activeRequirements()
    {
        return $this->requirements()->where('is_active', true);
    }
    
    /**
     * Get the required requirements for the project.
     */
    public function requiredRequirements()
    {
        return $this->requirements()->where('is_required', true);
    }
    
    /**
     * Get the chat messages for the project's candidates.
     */
    public function candidateChatMessages(): HasManyThrough
    {
        return $this->hasManyThrough(CandidateChatMessage::class, Candidate::class);
    }

    /**
     * Get the latest job description for the project.
     */
    public function latestJobDescription()
    {
        return $this->jobDescriptions()->latest()->first();
    }

    /**
     * Get the published job description for the project.
     */
    public function publishedJobDescription()
    {
        return $this->jobDescriptions()->where('status', 'published')->latest()->first();
    }

    /**
     * Create a new job description from the project data.
     */
    public function createJobDescription(array $additionalData = []): JobDescription
    {
        $jobDescription = new JobDescription([
            'title' => $this->job_title ?? $this->title,
            'overview' => $this->overview,
            'responsibilities' => $this->responsibilities,
            'requirements_non_negotiable' => $this->requirements_non_negotiable,
            'requirements_preferred' => $this->requirements_preferred,
            'compensation_range' => $this->compensation_range,
            'benefits' => $this->benefits,
            'location' => $this->location,
            'disclaimer' => $this->disclaimer,
            'experience_level' => $this->experience_level,
            'employment_type' => $this->employment_type,
            'education_requirements' => $this->education_requirements,
            'skills_required' => $this->required_skills,
            'skills_preferred' => $this->preferred_skills,
            'status' => 'draft',
            'user_id' => $this->user_id,
        ]);

        // Merge additional data
        foreach ($additionalData as $key => $value) {
            $jobDescription->$key = $value;
        }

        $this->jobDescriptions()->save($jobDescription);
        
        return $jobDescription;
    }
}
