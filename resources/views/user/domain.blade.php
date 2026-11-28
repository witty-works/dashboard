<h1 class="ibarra-sub-title-h1 margin-top">
    {!! __('guidelines.privacy_settings_label') !!}
</h1>
@include('partials.banners')

{{-- Consent to being counted in the team's analytics: only while events are collected. --}}
@if (\App\Support\Analytics::collected())
<div>
    <div class="py-10">
        @livewire('team-analytics', ['model' => $user])
    </div>
</div>
@endif

@php 
$teamAllowList = !empty($user->currentTeam)
    && $user->currentTeam->getDomainListType() !== 'deny'
@endphp

@if(!$teamAllowList)
<div>
    <div class="py-10">
        @livewire('user-domain.form', ['model' => $user])
    </div>
</div>

<div>
    <div class="py-10">
        @livewire('user-domain.show', ['model' => $user])
    </div>
</div>
@endif

@if($user->currentTeam)
<div>
    <div class="py-10">
        @livewire('organization-domain.show', ['model' => $user->currentTeam, 'hide_actions' => true])
    </div>
</div>
@endif
