<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TemplateCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * Get the templates for this category.
     */
    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(JobDescriptionTemplate::class, 'job_description_template_category');
    }

    /**
     * Scope a query to find by slug.
     */
    public function scopeSlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }
}