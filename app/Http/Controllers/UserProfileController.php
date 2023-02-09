<?php

namespace App\Http\Controllers;

use App\Helpers\Hubspot;
use App\Jobs\SyncUserToNlpApi;
use App\Mail\TeamInvitationRequest as MailTeamInvitationRequest;
use App\Models\LanguageGuidelines;
use App\Models\TeamInvitationRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Laravel\Jetstream\Http\Controllers\Livewire\UserProfileController as BaseUserProfileController;

class UserProfileController extends BaseUserProfileController
{
    public function onboarding(Request $request)
    {
        $user = $request->user();
        if (empty($user)) {
            return redirect()->route('root');
        }

        $users = null;
        $requestInvite = null;
        if (
            (!$user->currentTeam
                || $user->currentTeam->getTotalUserCount() <= 1)
            && !$user->invitations->count()
        ) {
            try {
                $hubspot = new Hubspot();
                $company = $hubspot->findCompanyByUser($user);
            } catch (\Exception $e) {
            }

            if (!empty($company)) {
                $users = $this->getCompanyUsers($user);
                $requestInvite = false;
                foreach ($users as $companyUser) {
                    if ($companyUser->teamRole($companyUser->currentTeam)->key !== 'user') {
                        $requestInvite = true;
                        break;
                    }
                }
            }
        }

        return view('profile.onboarding', [
            'user' => $request->user(),
            'users' => $users,
            'request_invite' => $requestInvite,
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
                'request_invite' => 'boolean',
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
                $users = $this->getCompanyUsers($user);
                foreach ($users as $companyUser) {
                    if ($companyUser->teamRole($companyUser->currentTeam)->key != 'user') {
                        if (empty($teams[$companyUser->currentTeam->id])) {
                            $teams[$companyUser->currentTeam->id] = [
                                'team' => $companyUser->currentTeam,
                                'admins' => [$companyUser],
                            ];
                        } else {
                            $teams[$companyUser->currentTeam->id]['admins'][] = $companyUser;
                        }
                    }
                }

                foreach ($teams as $data) {
                    $invitationRequest = TeamInvitationRequest::firstOrCreate([
                        'user_id' => $user->id,
                        'team_id' => $data['team']->id,
                    ]);

                    if ($invitationRequest->wasRecentlyCreated) {
                        Mail::to($data['admins'])->send(new MailTeamInvitationRequest($invitationRequest));

                        dispatch(new SyncUserToNlpApi($data['team']->owner, 'high'));
                    }
                }
            }
        }

        return redirect()->route('root');
    }

    protected function getCompanyUsers($user)
    {
        return User::where('email', '!=', $user->email)
            ->where('id', '!=', $user->id)
            ->where('current_team_id', '!=', $user->team_id)
            ->where('email', 'LIKE', '%@' . $user->getEmailDomain())
            ->get();
    }
}
