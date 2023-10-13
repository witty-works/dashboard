<?php

namespace App\Http\Controllers;

use App\Jobs\SyncUserToNlpApi;
use App\Mail\TeamInvitationRequest as MailTeamInvitationRequest;
use App\Models\LanguageGuidelines;
use App\Models\TeamInvitationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Laravel\Jetstream\Http\Controllers\Livewire\UserProfileController as BaseUserProfileController;

class UserProfileController extends BaseUserProfileController
{
    const HOW_DID_YOU_FIND = [
        '' => 'content.please_select',
        'search_engine' => 'content.search_engine',
        'recommended' => 'content.recommended',
        'social_media' => 'content.social_media',
        'blog' => 'content.blog',
        'consultant' => 'content.consultant',
        'other' => 'content.other'
    ];


    public function storeOnboarding(Request $request)
    {
        $user = $request->user();
        if (!empty($user)) {
            $validated = $request->validate([
                'role' => 'required|in:executive,lead,employee',
                'languages' => 'nullable',
                'company_name' => 'nullable|max:100',
                'how_did_you_find' => 'nullable|in:' . implode(',', array_keys(self::HOW_DID_YOU_FIND)),
                'request_invite' => 'boolean',
            ]);

            $user->role = $validated['role'];
            if (!empty($validated['company_name'])) {
                $user->company_name = $validated['company_name'];
            }
            if (!empty($validated['how_did_you_find'])) {
                $user->how_did_you_find = $validated['how_did_you_find'];
            }
            $user->save();

            if (!empty($validated['languages'])) {
                $preferredVariants = $validated['languages'] == 'en'
                    ? ['en-US'] : ['de-DE'];
                $languageGuidelines = LanguageGuidelines::firstOrNew(['user_id' => $user->id]);
                $languageGuidelines->preferred_variants = $preferredVariants;
                $languageGuidelines->customized = false;
                $languageGuidelines->save();
            }

            if ($user->currentTeam && $user->ownsTeam($user->currentTeam)) {
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

            if (!empty($validated['request_invite'])) {
                $teams = [];
                $users = $user->getCompanyUsers();
                foreach ($users as $companyUser) {
                    $teamRole = $companyUser->teamRole($companyUser->currentTeam);
                    if ($teamRole && $teamRole->key != 'user') {
                        if (empty($teams[$companyUser->currentTeam->id])) {
                            $teams[$companyUser->currentTeam->id] = [
                                'team' => $companyUser->currentTeam,
                                'admins' => [$companyUser],
                                'language' => $companyUser->language ?? 'en',
                            ];
                        } else {
                            $teams[$companyUser->currentTeam->id]['admins'][] = $companyUser;
                            if ($companyUser->language === 'en') {
                                $teams[$companyUser->currentTeam->id]['language'] = $companyUser->language;
                            }
                        }
                    }
                }

                foreach ($teams as $data) {
                    $invitationRequest = TeamInvitationRequest::firstOrCreate([
                        'user_id' => $user->id,
                        'team_id' => $data['team']->id,
                    ]);

                    if ($invitationRequest->wasRecentlyCreated) {
                        Mail::to($data['admins'])->send(new MailTeamInvitationRequest($invitationRequest, $data['language']));

                        dispatch(new SyncUserToNlpApi($data['team']->owner, 'high'));
                    }
                }
            }
        }

        return redirect(url()->previous());
    }
}
