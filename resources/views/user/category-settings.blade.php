<div>
    <h1 class="ibarra-sub-title-h1 margin-top">
        {{ __('guidelines.language') }}
    </h1>

    @livewire('user-category-settings.intro', ['model' => $user])

    @foreach ($categories as $category => $config)
    @livewire('user-category-settings.category', ['model' => $user, 'category' => $category, 'config' => $config])
    @endforeach

</div>
