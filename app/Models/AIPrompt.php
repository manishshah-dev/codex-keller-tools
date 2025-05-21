<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIPrompt extends Model
{
    use HasFactory;

    protected $table = 'ai_prompts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'feature',
        'name',
        'prompt_template',
        'parameters',
        'provider',
        'model',
        'is_default',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'parameters' => 'json',
        'is_default' => 'boolean',
    ];

    /**
     * Get the user that created the prompt.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include default prompts.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope a query to only include prompts for a specific feature.
     */
    public function scopeForFeature($query, $feature)
    {
        return $query->where('feature', $feature);
    }

    /**
     * Scope a query to only include prompts for a specific provider.
     */
    public function scopeForProvider($query, $provider)
    {
        return $query->where(function($q) use ($provider) {
            $q->where('provider', $provider)
              ->orWhereNull('provider');
        });
    }

    /**
     * Scope a query to only include prompts for a specific model.
     */
    public function scopeForModel($query, $model)
    {
        return $query->where(function($q) use ($model) {
            $q->where('model', $model)
              ->orWhereNull('model');
        });
    }

    /**
     * Get the formatted prompt with parameters replaced.
     */
    public function formatPrompt(array $data = []): string
    {
        $prompt = $this->prompt_template;
        
        foreach ($data as $key => $value) {
            $prompt = str_replace('{{' . $key . '}}', $value, $prompt);
        }
        
        return $prompt;
    }

    /**
     * Create a copy of this prompt.
     */
    public function duplicate(): self
    {
        $duplicate = $this->replicate();
        $duplicate->name = $this->name . ' (Copy)';
        $duplicate->is_default = false;
        $duplicate->save();
        
        return $duplicate;
    }
}