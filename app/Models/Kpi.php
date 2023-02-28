<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public static function getWritingStreakPast30Days($model)
    {
        $columnName = $model instanceof User ? 'user_id' : 'team_id';

        return Kpi::where($columnName, $model->id)
            ->where('kpi', Kpi::WRITING_STREAK)
            ->whereDate('date', '>', now()->subDays(30))->sum('value');
    }
}
