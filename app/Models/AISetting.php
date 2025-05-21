<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AISetting extends Model
{
    use HasFactory;

    // Removed static $providerModels array - will fetch dynamically via ModelRegistryService

    protected $table = 'ai_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'provider',
        'name',
        'api_key',
        'organization_id',
        'is_active',
        'is_default',
        'models',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'models' => 'json',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'api_key',
        'organization_id',
    ];

    /**
     * Get the prompts for this AI provider.
     */
    public function prompts(): HasMany
    {
        return $this->hasMany(AIPrompt::class, 'provider', 'provider');
    }

    /**
     * Get the usage logs for this AI provider.
     */
    public function usageLogs(): HasMany
    {
        return $this->hasMany(AIUsageLog::class, 'provider', 'provider');
    }

    /**
     * Scope a query to only include active providers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include default providers.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope a query to find by provider.
     */
    public function scopeProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Check if this provider supports a specific model.
     */
    public function supportsModel(string $model): bool
    {
        return in_array($model, $this->models ?? []);
    }
}