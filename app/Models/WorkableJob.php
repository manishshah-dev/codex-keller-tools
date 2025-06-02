<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkableJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'workable_id',
        'title',
        'full_title',
        'shortcode',
        'department',
        'location',
        'url',
        'state',
        'job_created_at',
        'data',
    ];

    protected $casts = [
        'job_created_at' => 'datetime',
        'data' => 'array',
    ];
}
