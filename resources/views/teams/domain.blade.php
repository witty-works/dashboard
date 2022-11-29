<div class="ibarra-sub-title-h1 margin-top">
    {{ __('guidelines.privacy_settings_label') }}
</div>

<div>
    <div class="py-10">
        @livewire('teams.store-context', ['team' => $team])
    </div>
</div>
          
<div>
    <div class="py-10">
        @livewire('teams.analytics-user-access', ['team' => $team])
    </div>
</div>


<div>
    <div class="py-10">
        @livewire('organization-domain.type', ['team' => $team])
    </div>
</div>

<div>
    <div class="py-10">
        @livewire('organization-domain.form', ['team' => $team])
    </div>
</div>

<div>
    <div class="py-10">
        @livewire('organization-domain.show', ['team' => $team])
    </div>
</div>
