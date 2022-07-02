<?php

namespace App\Models;

class LanguageGuidelines extends UserGuidelines
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->fillable[] = 'team_id';
    }

    static public function getTeamGuidelines(User $user)
    {
        $team = $user->currentTeam;

        if (!$team) {
            return null;
        }

        return self::where('team_id', $team->id)->first();
    }

    static public function isForcedOnTeam(User $user, $section)
    {
        $teamGuidelines = self::getTeamGuidelines($user);

        if (!$teamGuidelines) {
            return false;
        }

        if (in_array($section, GuidelinesInterface::DISABLED_CATEGORIES)) {
            return in_array($section, $teamGuidelines->disabled_categories_force) ? 'locked' : false;
        }

        return $teamGuidelines->{$section . '_force'} ? 'locked' : false;
    }
}
