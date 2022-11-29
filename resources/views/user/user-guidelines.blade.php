<div>
    <div class="ibarra-sub-title-h1 margin-top">
        {{ __('guidelines.language_settings_label') }}
    </div>

    @livewire('user-guidelines.intro', ['user' => $user])

    @livewire('user-guidelines.language', ['user' => $user])

    @livewire('user-guidelines.english', ['user' => $user])

    @livewire('user-guidelines.german', ['user' => $user])

    @livewire('user-guidelines.expert-mode', ['user' => $user])

    @livewire('user-guidelines.inspirations', ['user' => $user])
</div>