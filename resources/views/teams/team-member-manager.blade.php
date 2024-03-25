<div>
@if (Gate::check('viewUserCreateForm', $team))
    <!-- Add Team Member -->
    <section id="add-team-member" aria-labelledby="sectionTitle">
        <h2 id="sectionTitle" class="ibarra-sub-title-h1 margin-top">{{ __('content.manage_members') }}</h2>

        @livewire('teams.update-team-name-form-cancel', ['team' => $team])

        <div class="pt-10">
        <x-form-section submit="addTeamMember">
            <x-slot name="title">{{ __('content.add_team_member') }}</x-slot>

            <x-slot name="description">
                {{ trans_choice('content.add_a_new_team_member', $team->getUserLicensesCount(), ['max_count' => $team->getUserLicensesCount()]) }}

                @if (session()->has('teams_invitation_request_message'))
                <p role="alert" class="lato-small-text-p margin-bottom limit-reached">{{ session('teams_invitation_request_message') }}</p>
                @endif

                <!-- Limit reached -->
                @if($team->getUserLicensesLimitReached(true))
                <p role="alert" class="lato-small-text-p margin-bottom limit-reached">
                    @if($team->getUserLicensesLimitReached())
                    {!! __('teams.user_limit_reached_error') !!}
                    @else
                    {!! __('teams.pending_user_limit_reached_error') !!}
                    @endif

                    @if(Auth::user()->ownsTeam($team))
                    <a role="button" class="button primary-button-red" href="{{ route('teams.subscription') }}">
                        {{ __('teams.add_licenses') }}
                    </a>
                    @endif
                </p>
                @endif
            </x-slot>

            <x-slot name="form">
                <div role="group" class="col-span-6">
                    <label class="lato-paragraph-text-p margin-bottom">{{ __('content.please_provide_the_email_address') }}</label>
                </div>

                <!-- Member Email -->
                <div class="w-full margin-bottom">
                    <x-label id="email-label" class="lato-small-text-p" for="email" value="{{ __('content.email') }}" />
                    <x-input id="email" type="email" class=" mt-1 block w-full" wire:model="addTeamMemberForm.email" disabled="{{ !Gate::check('addTeamMember', $team) }}" aria-describedby="email-label" />
                    <x-input-error id="emailError" for="email"></x-input-error>
                </div>

                <!-- Role -->
                @if (count($this->roles) > 0)
                    <div role="group" class="col-span-6 lg:col-span-4">
                        <x-label class="lato-small-text-p" for="role" value="{{ __('content.role') }}" />
                        <x-input-error for="role" class="mt-2" />
                        <div role="listbox" class="relative z-0 mt-1 border border-gray-200 rounded-lg">
                            @foreach ($this->roles as $index => $role)
                                <button role="option" aria-selected="{{ $addTeamMemberForm['role'] === $role->key }}" type="button" class="relative px-4 py-3 inline-flex w-full rounded-lg"
                                        wire:click="$set('addTeamMemberForm.role', '{{ $role->key }}')">
                                    <div class="{{ $addTeamMemberForm['role'] !== $role->key ? 'opacity-50' : '' }}">
                                        <!-- Role Name -->
                                        <div class="flex items-center">
                                            <div class="lato-paragraph-text-p lato-small-paragraph-title-h4 {{ $addTeamMemberForm['role'] == $role->key ? 'font-semibold' : '' }}">
                                                {{ $role->name }}
                                            </div>

                                            @if ($addTeamMemberForm['role'] == $role->key)
                                                <svg class="ml-2 h-5 w-5 text-green-400 lato-small-text-p" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            @endif
                                        </div>

                                        <!-- Role Description -->
                                        <div class="mt-2 lato-small-text-p text-left">{{ $role->description }}</div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </x-slot>

            <x-slot name="actions">
                <div role="group" class="flex flex-row align-middle items-center">
                    <x-button>
                        {{ __('content.add') }}
                    </x-button>

                    <a role="button" wire:click="cancel()" class="button secondary-button-red ">{{ __('content.cancel') }}</a>
                    <x-action-message role="status" class="m-3" on="saved">{{ __('content.added') }}</x-action-message>
                </div>
            </x-slot>
        </x-form-section>
        </div>
    </section>
@endif

@if ($team->teamInvitations->isNotEmpty() && Gate::check('viewUserCreateForm', $team))
    <!-- Team Member Invitations -->
    <div class="py-5">
        <x-action-section>
            <x-slot name="title">
                {{ trans_choice('content.pending_team_invitations', $team->teamInvitations->count(), ['count' => $team->teamInvitations->count()]) }}
            </x-slot>

            <x-slot name="description">
                {{ __('content.these_people_have_been_invited') }}
            </x-slot>

            <x-slot name="content">
                <div class="space-y-6">
                    @foreach ($team->teamInvitations as $invitation)
                        <div class="flex items-center justify-between">
                            <div class="lato-paragraph-text-p">{{ $invitation->email }}</div>
                            <div class="flex items-center">
                                @if (Gate::check('removeTeamMember', $team))
                                    <!-- Cancel Team Invitation -->
                                    <button class="cursor-pointer ml-6 lato-paragraph-text-p-red"
                                            wire:click="cancelTeamInvitation({{ $invitation->id }})"
                                            aria-label="Cancel invitation for {{ $invitation->email }}">
                                        {{ __('content.cancel') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-slot>
        </x-action-section>
    </div>
@endif

@if ($team->invitationRequests->isNotEmpty() && Gate::check('viewUserCreateForm', $team))
    <!-- Team Member Invitations -->
    <div class="py-5" id="requests" role="tablist" aria-label="Team member invitations">
        <x-action-section>
            <x-slot name="title">
                {{ trans_choice('content.pending_team_invitation_requests', $team->invitationRequests->count(), ['count' => $team->invitationRequests->count()]) }}
            </x-slot>

            <x-slot name="description">
                {{ __('content.these_people_have_requested_an_invite') }}

                @if($team->getUserLicensesLimitReached(true))
                <p role="alert" class="lato-small-text-p margin-bottom limit-reached">
                    @if($team->getUserLicensesLimitReached())
                    {!! __('teams.user_limit_reached_error') !!}
                    @else
                    {!! __('teams.pending_user_limit_reached_error') !!}
                    @endif

                    @if(Auth::user()->ownsTeam($team))
                    <a role="button" class="button primary-button-red" href="{{ route('teams.subscription') }}">
                        {{ __('teams.add_licenses') }}
                    </a>
                    @endif
                </p>
                @endif
            </x-slot>

            <x-slot name="content">
                <div class="space-y-6">
                    @foreach ($team->invitationRequests as $invitationRequest)
                        <div class="flex items-center justify-between">
                            <div class="lato-paragraph-text-p">{{ $invitationRequest->user->email }}</div>
                            <div class="flex items-center">
                                @if (Gate::check('removeTeamMember', $team))
                                    <button class="cursor-pointer ml-6 lato-paragraph-text-p-red"
                                            wire:click="cancelTeamInvitationRequest({{ $invitationRequest->id }})"
                                            aria-label="Cancel invitation request for {{ $invitationRequest->user->email }}">
                                        {{ __('content.cancel') }}
                                    </button>
                                    <button class="cursor-pointer ml-6 lato-paragraph-text-p-red"
                                            wire:click="acceptTeamInvitationRequest({{ $invitationRequest->id }})"
                                            aria-label="Accept invitation request for {{ $invitationRequest->user->email }}">
                                        {{ __('content.accept') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-slot>
        </x-action-section>
    </div>
@endif

@if ($team->users->isNotEmpty())
    <!-- Manage Team Members -->
    <div class="py-5">
        <x-action-section>
            <x-slot name="title">
                {{ trans_choice('content.team_members', $team->users->count(), ['count' => $team->users->count()]) }}
            </x-slot>

            <x-slot name="description">
                {{ __('content.all_of_the_people') }}
            </x-slot>

            <!-- Team Member List -->
            <x-slot name="content">
                <div class="space-y-6">
                    @foreach ($team->users->sortBy('name') as $user)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="ml-4">
                                    <a href="mailto:{{ $user->email }}">{{ $user->name }}</a>
                                    @if ($user->licenseTeam)
                                        @if ($user->isUserLicensedToTeam($team))
                                            ({{ __('teams.active_license') }})
                                        @else
                                        ({{ __('teams.active_license_on_team', ['team_name' => $user->licenseTeam->name]) }})
                                        @endif
                                    @else
                                        ({{ __('teams.no_active_license') }})
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center">
                                <!-- Manage Team Member Role -->
                                @if (Gate::check('update', $team) && $team->subscribed() && Laravel\Jetstream\Jetstream::hasRoles())
                                    <button class="ml-2 text-sm text-gray-400 underline" wire:click="manageRole('{{ $user->id }}')">
                                        {{ Laravel\Jetstream\Jetstream::findRole($user->membership->role)->name }}
                                    </button>
                                @elseif (Laravel\Jetstream\Jetstream::hasRoles())
                                    <div class="ml-2 text-sm text-gray-400">
                                        {{ Laravel\Jetstream\Jetstream::findRole($user->membership->role)->name }}
                                    </div>
                                @endif

                                <!-- Leave Team -->
                                @if ($this->user->id === $user->id)
                                    <button class="cursor-pointer ml-6 text-sm text-red-500" wire:click="$toggle('confirmingLeavingTeam')">
                                        {{ __('content.leave') }}
                                    </button>

                                <!-- Remove Team Member -->
                                @elseif (Gate::check('removeTeamMember', $team))
                                    <button class="cursor-pointer ml-6 text-sm text-red-500" wire:click="confirmTeamMemberRemoval('{{ $user->id }}')">
                                        {{ __('content.remove') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-slot>
        </x-action-section>
    </div>
@endif

    <!-- Role Management Modal -->
    <x-dialog-modal wire:model.live="currentlyManagingRole">
        <x-slot name="title">
            {{ __('content.manage_role') }}
        </x-slot>

        <x-slot name="content">
            <div class="relative z-0 mt-1 border border-gray-200 rounded-lg cursor-pointer">
                @foreach ($this->roles as $index => $role)
                    <button type="button" class="relative px-4 py-3 inline-flex w-full rounded-lg focus:z-10 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 {{ $index > 0 ? 'border-t border-gray-200 rounded-t-none' : '' }} {{ ! $loop->last ? 'rounded-b-none' : '' }}"
                                    wire:click="$set('currentRole', '{{ $role->key }}')">
                        <div class="{{ $currentRole !== $role->key ? 'opacity-50' : '' }}">
                            <!-- Role Name -->
                            <div class="flex items-center">
                                <div class="text-sm text-gray-600 {{ $currentRole == $role->key ? 'font-semibold' : '' }}">
                                    {{ $role->name }}
                                </div>

                                @if ($currentRole == $role->key)
                                    <svg class="ml-2 h-5 w-5 text-green-400" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                @endif
                            </div>

                            <!-- Role Description -->
                            <div class="mt-2 text-xs text-gray-600">
                                {{ $role->description }}
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="stopManagingRole" wire:loading.attr="disabled">
                {{ __('content.cancel') }}
            </x-secondary-button>

            <x-button class="ml-3" wire:click="updateRole" wire:loading.attr="disabled">
                {{ __('content.save') }}
            </x-button>
        </x-slot>
    </x-dialog-modal>

    <!-- Leave Team Confirmation Modal -->
    <x-confirmation-modal wire:model.live="confirmingLeavingTeam">
        <x-slot name="title">
            {{ __('content.leave_team') }}
        </x-slot>

        <x-slot name="content">
            {{ __('content.are_you_sure_you_would_like_to_leave') }}
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingLeavingTeam')" wire:loading.attr="disabled">
                {{ __('content.cancel') }}
            </x-secondary-button>

            <x-danger-button class="ml-3" wire:click="leaveTeam" wire:loading.attr="disabled">
                {{ __('content.leave') }}
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>

    <!-- Remove Team Member Confirmation Modal -->
    <x-confirmation-modal wire:model.live="confirmingTeamMemberRemoval">
        <x-slot name="title">
            {{ __('content.remove_team_member') }}
        </x-slot>

        <x-slot name="content">
            {{ __('content.are_you_sure_you_would_like_to_remove') }}
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingTeamMemberRemoval')" wire:loading.attr="disabled">
                {{ __('content.cancel') }}
            </x-secondary-button>

            <x-danger-button class="ml-3" wire:click="removeTeamMember" wire:loading.attr="disabled">
                {{ __('content.remove') }}
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>

</div>