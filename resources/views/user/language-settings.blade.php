<div>
    <h1 class="ibarra-sub-title-h1 margin-top">
        {!! __('guidelines.language_settings_label') !!}
    </h1>

    @if($user->isPremium())
    @livewire('user-language-settings.intro', ['model' => $user])
    @endif

    @livewire('user-language-settings.language', ['model' => $user])

    @livewire('user-language-settings.orthography', ['model' => $user])

    @livewire('user-language-settings.generic_masculine', ['model' => $user])

    @livewire('user-language-settings.german', ['model' => $user])

    @livewire('user-language-settings.inspirations', ['model' => $user])
</div>
