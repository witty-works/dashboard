<?php

namespace App\Http\Controllers;

use App\Events\UserCompanyUpdated;
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
                event(new UserCompanyUpdated($user));
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
                // check for organization users that own teams and are subscribed
                $users = $user->getOrganizationUsers(true, true);
                if ($users->count() == 0) {
                    // check for organization users that own teams
                    $users = $user->getOrganizationUsers(true);
                }
                foreach ($users as $organizationUser) {
                    if (empty($teams[$organizationUser->currentTeam->id])) {
                        $teams[$organizationUser->currentTeam->id] = [
                            'team' => $organizationUser->currentTeam,
                            'admins' => [$organizationUser],
                            'language' => $organizationUser->language ?? 'en',
                        ];
                    } else {
                        $teams[$organizationUser->currentTeam->id]['admins'][] = $organizationUser;
                        if ($organizationUser->language === 'en') {
                            $teams[$organizationUser->currentTeam->id]['language'] = $organizationUser->language;
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
