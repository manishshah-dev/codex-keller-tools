<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JobDescription extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'user_id',
        'title',
        'overview',
        'responsibilities',
        'requirements_non_negotiable',
        'requirements_preferred',
        'compensation_range',
        'benefits',
        'location',
        'disclaimer',
        'industry',
        'experience_level',
        'employment_type',
        'education_requirements',
        'skills_required',
        'skills_preferred',
        'template_used',
        'version',
        'status',
        'export_format',
        'export_path',
        'last_exported_at',
        'ai_provider',
        'ai_model',
        'generated_at',
        'generation_parameters',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_exported_at' => 'datetime',
        'generated_at' => 'datetime',
        'generation_parameters' => 'json',
    ];

    /**
     * Get the project that owns the job description.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user that created the job description.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the qualifying questions for the job description.
     */
    public function qualifyingQuestions(): HasMany
    {
        return $this->hasMany(QualifyingQuestion::class);
    }

    /**
     * Get the template used for this job description.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(JobDescriptionTemplate::class, 'template_used', 'id');
    }

    /**
     * Scope a query to only include job descriptions with a specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include job descriptions for a specific industry.
     */
    public function scopeIndustry($query, $industry)
    {
        return $query->where('industry', $industry);
    }

    /**
     * Generate a new version of this job description.
     */
    public function createNewVersion(): self
    {
        $newVersion = $this->replicate();
        $newVersion->version = $this->version + 1;
        $newVersion->status = 'draft';
        $newVersion->save();
        
        // Copy qualifying questions to the new version
        foreach ($this->qualifyingQuestions as $question) {
            $newQuestion = $question->replicate();
            $newQuestion->job_description_id = $newVersion->id;
            $newQuestion->save();
        }
        
        return $newVersion;
    }

    /**
     * Check if the job description is complete.
     */
    public function isComplete(): bool
    {
        return !empty($this->title) && 
               !empty($this->overview) && 
               !empty($this->responsibilities) && 
               !empty($this->requirements_non_negotiable);
    }

    /**
     * Get the export URL for the job description.
     */
    public function getExportUrl(string $format = 'pdf'): string
    {
        return route('job-descriptions.export', [
            'jobDescription' => $this->id,
            'format' => $format
        ]);
    }
}