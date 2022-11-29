<div>
    <div class="ibarra-sub-title-h1 margin-bottom">
        {{ __('guidelines.language_settings_label') }}
    </div>

    @livewire('organization-guidelines.language', ['team' => $team])

    @livewire('organization-guidelines.english', ['team' => $team])

    @livewire('organization-guidelines.german', ['team' => $team])

    @livewire('organization-guidelines.expert-mode', ['team' => $team])

    @livewire('organization-guidelines.inspirations', ['team' => $team])

    @livewire('organization-guidelines.inclusive', ['team' => $team])

    @livewire('organization-guidelines.style', ['team' => $team])

    @livewire('organization-guidelines.orthography', ['team' => $team])
</div>