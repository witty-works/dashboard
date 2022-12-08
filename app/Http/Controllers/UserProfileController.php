<?php

namespace App\Http\Controllers;

use App\Models\GuidelinesInterface;
use App\Models\LanguageGuidelines;
use Illuminate\Http\Request;
use Laravel\Jetstream\Http\Controllers\Livewire\UserProfileController as BaseUserProfileController;

class UserProfileController extends BaseUserProfileController
{
    public function onboarding(Request $request)
    {
        $user = $request->user();
        if (empty($user)) {
            return redirect()->route('root');
        }

        return view('profile.onboarding', [
            'user' => $request->user(),
        ]);
    }

    public function storeOnboarding(Request $request)
    {
        $user = $request->user();
        if (!empty($user)) {
            $validated = $request->validate([
                'role' => 'required|in:executive,lead,employee',
                'languages' => 'nullable',
                'company_name' => 'nullable',
            ]);

            $user->role = $validated['role'];
            $user->save();

            if (!empty($validated['languages'])) {
                $preferredVariants = $validated['languages'] == 'en'
                    ? ['en-US'] : ['de-DE'];
                $languageGuidelines = LanguageGuidelines::firstOrNew(['user_id' => $user->id]);
                $languageGuidelines->preferred_variants = $preferredVariants;
                $languageGuidelines->customized = false;
                $languageGuidelines->save();
            }

            if ($user->currentTeam) {
                if (!empty($preferredVariants)) {
                    $languageGuidelines = LanguageGuidelines::firstOrNew(['team_id' => $user->currentTeam->id]);
                    $languageGuidelines->preferred_variants = $preferredVariants;
                    $languageGuidelines->customized = false;
                    $languageGuidelines->save();
                }

                if (!empty($validated['company_name'])) {
                    $user->currentTeam->name = $validated['company_name'];
                    $user->currentTeam->save();
                }
            }
        }

        return redirect()->route('root');
    }
}
