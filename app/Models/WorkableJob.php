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
    ];
}
