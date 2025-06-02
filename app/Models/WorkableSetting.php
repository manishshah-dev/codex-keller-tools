<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkableSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subdomain',
        'api_token',
        'is_active',
        'is_default',
    ];

    protected $hidden = ['api_token'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];
}
