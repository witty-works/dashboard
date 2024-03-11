<?php

namespace App\Http\Controllers\Livewire;

use App\Console\Commands\SyncToHubspotCategoriesCommand;
use App\Models\LanguageGuidelines;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserGuidelinesController extends Controller
{
    const CATEGORY_SETTINGS = 'category-settings';
    const LANGUAGE_SETTINGS = 'language-settings';
    const TERM_REPLACEMENTS = 'term_replacements';
    const FALSE_POSITIVES = 'false_positives';
    const DOMAINS = 'domains';

    public function resetLanguageSettings(Request $request)
    {
        $user = $request->user();

        if ($user->currentTeam) {
            $teamLanguageGuidelines = LanguageGuidelines::where('team_id', $user->currentTeam->id)->first();

            if ($teamLanguageGuidelines) {
                $languageGuidelines = LanguageGuidelines::firstOrNew(['user_id' => $user->id]);

                $languageGuidelines->resetSettingsToTeam($teamLanguageGuidelines, self::LANGUAGE_SETTINGS);
            }
        }

        return redirect()->route('user.language-settings');
    }

    public function falsePositives(Request $request)
    {
        return $this->settings($request, 'false_positives');
    }

    public function termReplacements(Request $request)
    {
        return $this->settings($request, 'term_replacements');
    }

    public function domains(Request $request)
    {
        return $this->settings($request, 'domains');
    }

    public function languageSettings(Request $request)
    {
        return $this->settings($request, 'language-settings');
    }

    public function categorySettings(Request $request)
    {
        return $this->settings($request, 'category-settings');
    }

    protected function settings(Request $request, $tab = null)
    {
        $user = $request->user();
        if (!$user->isUserLicensedToTeam()) {
            return redirect()->route('profile.show');
        }

        $tabs = [
            self::CATEGORY_SETTINGS => 'user.category-settings',
            self::LANGUAGE_SETTINGS => 'user.language-settings',
            self::TERM_REPLACEMENTS => 'user.dictionary',
            self::FALSE_POSITIVES => 'user.ignored-words',
            self::DOMAINS => 'user.privacy-settings',
        ];

        return view('user/language-guidelines', [
            'user' => $user,
            'tab' => $tab,
            'tabs' => $tabs,
            'categories' => SyncToHubspotCategoriesCommand::loadTableData('categories'),
        ]);
    }
}
