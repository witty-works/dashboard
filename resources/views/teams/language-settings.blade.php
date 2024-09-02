<div>
    <h1 class="ibarra-sub-title-h1 margin-top margin-bottom">
        {!! __('guidelines.language_settings_label') !!}
    </h1>

    @livewire('organization-language-settings.language', ['model' => $team])

    @livewire('organization-language-settings.orthography', ['model' => $team])

    @livewire('organization-language-settings.generic_masculine', ['model' => $team])

    @livewire('organization-language-settings.german', ['model' => $team])

    @livewire('organization-language-settings.inspirations', ['model' => $team])

</div>