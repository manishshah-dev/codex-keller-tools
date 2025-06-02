<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkableJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'workable_setting_id',
        'workable_job_id',
        'title',
        'full_title',
        'shortcode',
        'state',
        'department',
        'url',
        'application_url',
        'shortlink',
        'location_str',
        'country',
        'country_code',
        'region',
        'city',
        'zip_code',
        'telecommuting',
        'workplace_type',
        'salary_currency',
        'raw_location_data',
        'raw_data',
        'workable_created_at',
        'workable_updated_at',
    ];

    protected $casts = [
        'telecommuting' => 'boolean',
        'raw_location_data' => 'array',
        'raw_data' => 'array',
        'workable_created_at' => 'datetime',
        'workable_updated_at' => 'datetime',
    ];

    public function workableSetting(): BelongsTo
    {
        return $this->belongsTo(WorkableSetting::class);
    }
}
