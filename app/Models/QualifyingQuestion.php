<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualifyingQuestion extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'job_description_id',
        'question',
        'description',
        'type',
        'options',
        'required',
        'order',
        'category',
        'is_knockout',
        'correct_answer',
        'is_ai_generated',
        'ai_provider',
        'ai_model',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'options' => 'json',
        'required' => 'boolean',
        'is_knockout' => 'boolean',
        'is_ai_generated' => 'boolean',
    ];

    /**
     * Get the job description that owns the qualifying question.
     */
    public function jobDescription(): BelongsTo
    {
        return $this->belongsTo(JobDescription::class);
    }

    /**
     * Scope a query to only include questions of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include knockout questions.
     */
    public function scopeKnockout($query, $isKnockout = true)
    {
        return $query->where('is_knockout', $isKnockout);
    }

    /**
     * Scope a query to only include required questions.
     */
    public function scopeRequired($query, $isRequired = true)
    {
        return $query->where('required', $isRequired);
    }

    /**
     * Scope a query to only include AI-generated questions.
     */
    public function scopeAiGenerated($query, $isAiGenerated = true)
    {
        return $query->where('is_ai_generated', $isAiGenerated);
    }

    /**
     * Scope a query to order by the order field.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Check if the question is a multiple choice question.
     */
    public function isMultipleChoice(): bool
    {
        return $this->type === 'multiple_choice';
    }

    /**
     * Check if the question is a yes/no question.
     */
    public function isYesNo(): bool
    {
        return $this->type === 'yes_no';
    }

    /**
     * Check if the question is a text question.
     */
    public function isText(): bool
    {
        return $this->type === 'text';
    }

    /**
     * Check if the question is a numeric question.
     */
    public function isNumeric(): bool
    {
        return $this->type === 'numeric';
    }

    /**
     * Get the options as an array.
     */
    public function getOptionsArray(): array
    {
        return $this->options ?? [];
    }
}