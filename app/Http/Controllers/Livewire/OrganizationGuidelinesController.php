<?php

namespace App\Http\Controllers\Livewire;

use App\Helpers\CategoryDataHelper;
use App\Http\Controllers\TeamControllerTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizationGuidelinesController extends UserGuidelinesController
{
    use TeamControllerTrait;

    protected function settings(Request $request, $tab = null)
    {
        $team = $this->getCurrentTeam($request);

        if (!Auth::user()->hasTeamPermission($team, 'edit_guidelines')) {
            abort(403);
        }

        $tabs = [
            self::CATEGORY_SETTINGS => 'teams.category-settings',
            self::LANGUAGE_SETTINGS => 'teams.language-settings',
            self::TERM_REPLACEMENTS => 'teams.dictionary',
            self::FALSE_POSITIVES => 'teams.ignored-words',
            self::DOMAINS => 'teams.privacy-settings',
        ];

        return view('teams/language-guidelines', [
            'team' => $team,
            'tab' => $tab,
            'tabs' => $tabs,
            'categories' => CategoryDataHelper::loadTableData('categories'),
        ]);
    }
}
