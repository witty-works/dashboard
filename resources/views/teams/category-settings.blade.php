<div>
    <div class="ibarra-sub-title-h1 margin-top">
        {{ __('guidelines.language') }}
    </div>

    @livewire('organization-category-settings.intro', ['model' => $team])

    @foreach ($categories as $category => $config)
    @livewire('organization-category-settings.category', ['model' => $team, 'category' => $category, 'config' => $config])
    @endforeach

</div>
