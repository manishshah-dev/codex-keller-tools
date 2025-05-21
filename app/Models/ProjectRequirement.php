<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectRequirement extends Model
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
        'type',
        'name',
        'description',
        'weight',
        'is_required',
        'is_active',
        'source',
        'created_by_chat',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'weight' => 'decimal:2',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'created_by_chat' => 'boolean',
    ];

    /**
     * Get the project that owns the requirement.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user that created the requirement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include active requirements.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include required requirements.
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope a query to only include requirements of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include requirements from a specific source.
     */
    public function scopeFromSource($query, $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope a query to only include requirements created by chat.
     */
    public function scopeCreatedByChat($query)
    {
        return $query->where('created_by_chat', true);
    }

    /**
     * Scope a query to order requirements by weight.
     */
    public function scopeOrderByWeight($query, $direction = 'desc')
    {
        return $query->orderBy('weight', $direction);
    }

    /**
     * Get the requirement's badge class based on type.
     */
    public function getTypeBadgeClassAttribute(): string
    {
        return match($this->type) {
            'skill' => 'bg-blue-100 text-blue-800',
            'experience' => 'bg-green-100 text-green-800',
            'education' => 'bg-purple-100 text-purple-800',
            'certification' => 'bg-yellow-100 text-yellow-800',
            'language' => 'bg-pink-100 text-pink-800',
            'location' => 'bg-indigo-100 text-indigo-800',
            'industry' => 'bg-red-100 text-red-800',
            'tool' => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the requirement's weight as a percentage.
     */
    public function getWeightPercentageAttribute(): string
    {
        return number_format($this->weight * 100, 0) . '%';
    }
}