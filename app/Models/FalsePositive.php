<?php

namespace App\Models;

use App\Helpers\PosthogHelper;
use App\Jobs\SendEventToPosthog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class FalsePositive extends Model
{
    use HasFactory;
    use GuidelinesUpdateTrait;

    public function getExistsOnTeamAttribute()
    {
        $team = $this->user->currentTeam;

        if (!$team) {
            return false;
        }

        return self::where('team_id', $team->id)->where('false_positive', $this->false_positive)->exists();
    }

    public function dispatchEventToPosthog()
    {
        if ($this->user) {
            $model = $user = $this->user;
            $team = null;
            $event = PosthogHelper::STORE_FALSE_POSITIVE;
        } else {
            $user = Auth::user();
            $model = $team = $this->team;
            $event = PosthogHelper::STORE_TEAM_FALSE_POSITIVE;
        }

        $job = new SendEventToPosthog(
            $user,
            $event,
            [
                'total' => $model->falsePositives->count(),
                'false_positive' => $this->false_positive,
            ],
            !$this->wasRecentlyCreated,
            $team,
        );

        dispatch($job);
    }
}
