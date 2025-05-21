<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'candidate_id',
        'project_id',
        'user_id',
        'title',
        'summary',
        'headings',
        'metadata',
        'extracted_data',
        'interview_insights',
        'web_presence_data',
        'is_finalized',
        'finalized_at',
        'status',
        'ai_provider',
        'ai_model',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'headings' => 'array',
        'metadata' => 'array',
        'extracted_data' => 'array',
        'interview_insights' => 'array',
        'web_presence_data' => 'array',
        'is_finalized' => 'boolean',
        'finalized_at' => 'datetime',
    ];

    /**
     * Get the candidate that owns the profile.
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    /**
     * Get the project that the profile belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user that created the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include draft profiles.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include in-progress profiles.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope a query to only include completed profiles.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include finalized profiles.
     */
    public function scopeFinalized($query)
    {
        return $query->where('is_finalized', true);
    }

    /**
     * Get the formatted headings for display.
     */
    public function getFormattedHeadingsAttribute()
    {
        if (!$this->headings) {
            return [];
        }

        return collect($this->headings)->map(function ($heading) {
            return [
                'title' => $heading['title'] ?? '',
                'content' => $heading['content'] ?? [],
                'order' => $heading['order'] ?? 0,
            ];
        })->sortBy('order')->values()->all();
    }

    /**
     * Get the profile status badge class.
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'draft' => 'bg-gray-100 text-gray-800',
            'in_progress' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the profile completion percentage.
     */
    public function getCompletionPercentageAttribute()
    {
        $steps = [
            'has_title' => !empty($this->title),
            'has_summary' => !empty($this->summary),
            'has_headings' => !empty($this->headings),
            'is_finalized' => $this->is_finalized,
        ];

        $completed = count(array_filter($steps));
        $total = count($steps);

        return round(($completed / $total) * 100);
    }

    /**
     * Mark the profile as finalized.
     */
    public function finalize()
    {
        $this->update([
            'is_finalized' => true,
            'finalized_at' => now(),
            'status' => 'completed',
        ]);

        return $this;
    }

    /**
     * Create a new heading or update an existing one.
     */
    public function updateHeading(string $title, array $content, int $order = null)
    {
        $headings = $this->headings ?: [];
        
        // Find existing heading with the same title
        $index = collect($headings)->search(function ($heading) use ($title) {
            return ($heading['title'] ?? '') === $title;
        });

        if ($index !== false) {
            // Update existing heading
            $headings[$index]['content'] = $content;
            if ($order !== null) {
                $headings[$index]['order'] = $order;
            }
        } else {
            // Add new heading
            $headings[] = [
                'title' => $title,
                'content' => $content,
                'order' => $order ?? count($headings),
            ];
        }

        $this->update(['headings' => $headings]);
        
        return $this;
    }

    /**
     * Remove a heading by title.
     */
    public function removeHeading(string $title)
    {
        if (!$this->headings) {
            return $this;
        }

        $headings = collect($this->headings)->filter(function ($heading) use ($title) {
            return ($heading['title'] ?? '') !== $title;
        })->values()->all();

        $this->update(['headings' => $headings]);
        
        return $this;
    }
}