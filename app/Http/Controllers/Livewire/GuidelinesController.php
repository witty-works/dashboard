<?php

namespace App\Http\Controllers\Livewire;

use App\Http\Controllers\TeamControllerTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class GuidelinesController extends Controller
{
    use TeamControllerTrait;

    const CUSTOMIZE_WITTY = 'customize_witty';
    const TERM_REPLACEMENTS = 'term_replacements';
    const FALSE_POSITIVES = 'false_positives';

    public function customizeWitty(Request $request)
    {
        return $this->languageSettings($request, 'customize_witty');
    }

    public function falsePositives(Request $request)
    {
        return $this->languageSettings($request, 'false_positives');
    }

    public function termReplacements(Request $request)
    {
        return $this->languageSettings($request, 'term_replacements');
    }

    protected function languageSettings(Request $request, $tab = null)
    {
        $team = $this->getCurrentTeam($request);

        $tabs = [
            self::CUSTOMIZE_WITTY => 'customize-witty',
            self::TERM_REPLACEMENTS => 'term-replacements',
            self::FALSE_POSITIVES => 'ignore-words',
        ];

        return view('language-guidelines', [
            'team' => $team,
            'tab' => $tab,
            'tabs' => $tabs,
        ]);
    }
}
