<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
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
        'first_name',
        'last_name',
        'email',
        'phone',
        'location',
        'current_company',
        'current_position',
        'linkedin_url',
        'resume_path',
        'resume_text',
        'resume_parsed_data',
        'match_score',
        'status',
        'source',
        'workable_id',
        'notes',
        'last_analyzed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'resume_parsed_data' => 'json',
        'last_analyzed_at' => 'datetime',
        'analysis_details' => 'json', // Add cast for the new column
    ];

    /**
     * Get the project that owns the candidate.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user that created the candidate.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the chat messages for the candidate.
     */
    public function chatMessages(): HasMany
    {
        return $this->hasMany(CandidateChatMessage::class);
    }

    /**
     * Get the candidate's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Scope a query to only include candidates with a specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include candidates from a specific source.
     */
    public function scopeSource($query, $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope a query to only include candidates with a minimum match score.
     */
    public function scopeMinScore($query, $score)
    {
        return $query->where('match_score', '>=', $score);
    }

    /**
     * Scope a query to order candidates by match score.
     */
    public function scopeOrderByScore($query, $direction = 'desc')
    {
        return $query->orderBy('match_score', $direction);
    }

    /**
     * Scope a query to only include candidates that match a search term.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
              ->orWhere('last_name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('current_company', 'like', "%{$term}%")
              ->orWhere('current_position', 'like', "%{$term}%")
              ->orWhere('resume_text', 'like', "%{$term}%");
        });
    }

    /**
     * Get the candidate's resume URL.
     */
    public function getResumeUrlAttribute(): ?string
    {
        if ($this->resume_path) {
            return asset('storage/' . $this->resume_path);
        }
        
        return null;
    }

    /**
     * Get the candidate's status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'new' => 'bg-blue-100 text-blue-800',
            'contacted' => 'bg-purple-100 text-purple-800',
            'interviewing' => 'bg-yellow-100 text-yellow-800',
            'offered' => 'bg-green-100 text-green-800',
            'hired' => 'bg-green-500 text-white',
            'rejected' => 'bg-red-100 text-red-800',
            'withdrawn' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the candidate's match score as a percentage.
     */
    public function getMatchScorePercentageAttribute(): string
    {
        return number_format($this->match_score * 100, 0) . '%';
    }
}