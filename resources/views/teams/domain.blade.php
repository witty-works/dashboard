<div class="ibarra-sub-title-h1 margin-top">
    {!! __('guidelines.privacy_settings_label') !!}
</div>

<div>
    <div class="py-10">
        @livewire('teams.store-context', ['model' => $team])
    </div>
</div>
          
<div>
    <div class="py-10">
        @livewire('teams.analytics-user-access', ['model' => $team])
    </div>
</div>


<div>
    <div class="py-10">
        @livewire('organization-domain.type', ['model' => $team])
    </div>
</div>

<div>
    <div class="py-10">
        @livewire('organization-domain.form', ['model' => $team])
    </div>
</div>

<div>
    <div class="py-10">
        @livewire('organization-domain.show', ['model' => $team])
    </div>
</div>
