<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileCustomHeading extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'user_id',
        'name',
        'description',
        'is_default',
        'display_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Get the project that the custom heading belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user that created the custom heading.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include default headings.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope a query to only include headings for a specific project.
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope a query to only include global headings (not project-specific).
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('project_id');
    }

    /**
     * Scope a query to order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    /**
     * Get all headings for a project, including global defaults.
     */
    public static function getAllForProject($projectId)
    {
        return static::where(function ($query) use ($projectId) {
            $query->where('project_id', $projectId)
                  ->orWhereNull('project_id');
        })->orderBy('display_order')->get();
    }

    /**
     * Create a new heading from an AI suggestion.
     */
    public static function createFromSuggestion(array $suggestion, $projectId = null, $userId = null)
    {
        return static::create([
            'project_id' => $projectId,
            'user_id' => $userId ?? auth()->id(),
            'name' => $suggestion['heading'] ?? $suggestion['title'] ?? '',
            'description' => $suggestion['rationale'] ?? $suggestion['description'] ?? '',
            'is_default' => false,
            'display_order' => static::where('project_id', $projectId)->max('display_order') + 1,
        ]);
    }
}