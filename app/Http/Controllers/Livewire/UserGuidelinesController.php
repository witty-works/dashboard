<?php

namespace App\Http\Controllers\Livewire;

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
            self::CUSTOMIZE_WITTY => 'user.customize-witty',
            self::TERM_REPLACEMENTS => 'user.term-replacements',
            self::FALSE_POSITIVES => 'user.ignore-words',
            self::DOMAINS => 'user.domains',
        ];

        return view('user/language-guidelines', [
            'user' => $user,
            'tab' => $tab,
            'tabs' => $tabs,
        ]);
    }
}
