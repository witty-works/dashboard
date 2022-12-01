<div class="ibarra-sub-title-h1 margin-top">
    {{ __('guidelines.privacy_settings_label') }}
</div>

<div>
    <div class="py-10">
        @livewire('team-analytics', ['user' => $user])
    </div>
</div>

@php 
$teamAllowList = !empty($user->currentTeam)
    && $user->currentTeam->getDomainListType() !== 'deny'
@endphp

@if(!$teamAllowList)
<div>
    <div class="py-10">
        @livewire('user-domain.form', ['user' => $user])
    </div>
</div>

<div>
    <div class="py-10">
        @livewire('user-domain.show', ['user' => $user])
    </div>
</div>
@endif

@if($user->currentTeam)
<div>
    <div class="py-10">
        @livewire('organization-domain.show', ['team' => $user->currentTeam, 'hide_actions' => true])
    </div>
</div>
@endif
