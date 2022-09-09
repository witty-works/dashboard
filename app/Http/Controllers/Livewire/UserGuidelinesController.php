<?php

namespace App\Http\Controllers\Livewire;

use App\Models\LanguageGuidelines;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserGuidelinesController extends Controller
{
    const CUSTOMIZE_WITTY = 'customize_witty';
    const TERM_REPLACEMENTS = 'term_replacements';
    const FALSE_POSITIVES = 'false_positives';
    const DOMAINS = 'domains';

    public function customizeWitty(Request $request)
    {
        return $this->languageSettings($request, 'customize_witty');
    }

    public function reset(Request $request)
    {
        $user = $request->user();

        if ($user->currentTeam) {
            $teamLanguageGuidelines = LanguageGuidelines::where('team_id', $user->currentTeam->id)->first();

            if ($teamLanguageGuidelines) {
                $languageGuidelines = LanguageGuidelines::firstOrNew(['user_id' => $user->id]);

                $languageGuidelines->preferred_languages = $teamLanguageGuidelines->preferred_languages;
                $languageGuidelines->preferred_variants = $teamLanguageGuidelines->preferred_variants;
                $languageGuidelines->german_gender_ending = $teamLanguageGuidelines->german_gender_ending;
                $languageGuidelines->gendered_roles_format = $teamLanguageGuidelines->gendered_roles_format;
                $languageGuidelines->singular_they = $teamLanguageGuidelines->singular_they;
                $languageGuidelines->show_inspiration_alternatives = $teamLanguageGuidelines->show_inspiration_alternatives;
                $languageGuidelines->save();
            }
        }
        return redirect()->route('user.language-settings');
    }

    public function falsePositives(Request $request)
    {
        return $this->languageSettings($request, 'false_positives');
    }

    public function termReplacements(Request $request)
    {
        return $this->languageSettings($request, 'term_replacements');
    }

    public function domains(Request $request)
    {
        return $this->languageSettings($request, 'domains');
    }

    protected function languageSettings(Request $request, $tab = null)
    {
        $user = $request->user();

        $tabs = [
            self::CUSTOMIZE_WITTY => 'user.languagae-settings',
            self::TERM_REPLACEMENTS => 'user.dictionary',
            self::FALSE_POSITIVES => 'user.ignored-words',
            self::DOMAINS => 'user.privacy-settings',
        ];

        return view('user/language-guidelines', [
            'user' => $user,
            'tab' => $tab,
            'tabs' => $tabs,
        ]);
    }
}
