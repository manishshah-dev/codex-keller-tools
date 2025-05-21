<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateChatMessage extends Model
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
        'message',
        'is_user',
        'requirements_added',
        'requirements_removed',
        'ai_provider',
        'ai_model',
        'tokens_used',
        'cost',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_user' => 'boolean',
        'requirements_added' => 'json',
        'requirements_removed' => 'json',
        'tokens_used' => 'integer',
        'cost' => 'decimal:6',
    ];

    /**
     * Get the candidate that owns the chat message.
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    /**
     * Get the project that owns the chat message.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user that created the chat message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include user messages.
     */
    public function scopeUserMessages($query)
    {
        return $query->where('is_user', true);
    }

    /**
     * Scope a query to only include AI messages.
     */
    public function scopeAiMessages($query)
    {
        return $query->where('is_user', false);
    }

    /**
     * Scope a query to only include messages that added requirements.
     */
    public function scopeAddedRequirements($query)
    {
        return $query->whereNotNull('requirements_added');
    }

    /**
     * Scope a query to only include messages that removed requirements.
     */
    public function scopeRemovedRequirements($query)
    {
        return $query->whereNotNull('requirements_removed');
    }

    /**
     * Get the message class for styling.
     */
    public function getMessageClassAttribute(): string
    {
        return $this->is_user 
            ? 'bg-blue-100 text-blue-800 ml-auto' 
            : 'bg-gray-100 text-gray-800 mr-auto';
    }
}