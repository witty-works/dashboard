<div>
    <div class="ibarra-sub-title-h1 margin-top margin-bottom">
        {{ __('guidelines.language_settings_label') }}
    </div>

    @livewire('organization-language-settings.language', ['model' => $team])

    @livewire('organization-language-settings.german', ['model' => $team])

    @livewire('organization-language-settings.inspirations', ['model' => $team])

</div>