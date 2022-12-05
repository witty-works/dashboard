<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Kpi extends Model
{
    use HasFactory;

    const TEAM_COUNT = 'team_count';
    const LICENSE_COUNT = 'license_count';
    const WRITING_STREAK = 'writing_streak';

    protected $fillable = [
        'team_id',
        'user_id',
        'date',
        'kpi',
        'value',
    ];
}
