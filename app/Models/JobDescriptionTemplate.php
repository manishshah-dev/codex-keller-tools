<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JobDescriptionTemplate extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'industry',
        'job_level',
        'description',
        'overview_template',
        'responsibilities_template',
        'requirements_template',
        'benefits_template',
        'disclaimer_template',
        'is_default',
        'is_active',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that created the template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the job descriptions that use this template.
     */
    public function jobDescriptions(): HasMany
    {
        return $this->hasMany(JobDescription::class, 'template_used');
    }

    /**
     * Get the categories for this template.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(TemplateCategory::class, 'job_description_template_category');
    }

    /**
     * Scope a query to only include active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include default templates.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope a query to only include templates for a specific industry.
     */
    public function scopeIndustry($query, $industry)
    {
        return $query->where('industry', $industry);
    }

    /**
     * Scope a query to only include templates for a specific job level.
     */
    public function scopeJobLevel($query, $jobLevel)
    {
        return $query->where('job_level', $jobLevel);
    }

    /**
     * Apply this template to a job description.
     */
    public function applyToJobDescription(JobDescription $jobDescription): void
    {
        $jobDescription->update([
            'template_used' => $this->id,
            'overview' => $this->overview_template,
            'responsibilities' => $this->responsibilities_template,
            'requirements_non_negotiable' => $this->requirements_template,
            'benefits' => $this->benefits_template,
            'disclaimer' => $this->disclaimer_template,
            'industry' => $this->industry,
        ]);
    }

    /**
     * Create a copy of this template.
     */
    public function duplicate(): self
    {
        $duplicate = $this->replicate();
        $duplicate->name = $this->name . ' (Copy)';
        $duplicate->is_default = false;
        $duplicate->save();
        
        // Copy categories
        $duplicate->categories()->attach($this->categories->pluck('id'));
        
        return $duplicate;
    }
}