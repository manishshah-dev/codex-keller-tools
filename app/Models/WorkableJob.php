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
        'state',
        'department',
        'department_hierarchy',
        'url',
        'application_url',
        'shortlink',
        'location',
        'locations',
        'salary',
        'workable_created_at',
    ];

    protected $casts = [
        'department_hierarchy' => 'json',
        'location' => 'json',
        'locations' => 'json',
        'salary' => 'json',
        'workable_created_at' => 'datetime',
    ];
}
