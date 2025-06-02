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
        'shortcode',
        'department',
        'country',
        'city',
        'url',
        'raw',
        'job_created_at',
    ];

    protected $casts = [
        'raw' => 'array',
        'job_created_at' => 'datetime',
    ];
}
