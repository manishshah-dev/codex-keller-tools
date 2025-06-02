<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkableCandidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'workable_id',
        'name',
        'email',
        'phone',
        'job_title',
        'job_shortcode',
    ];
}
