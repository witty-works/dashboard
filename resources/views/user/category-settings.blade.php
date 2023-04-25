<div>
    <div class="ibarra-sub-title-h1 margin-top">
        {{ __('guidelines.language') }}
    </div>

    @if($user->subscribed())
    @livewire('user-category-settings.intro', ['model' => $user])
    @endif

    @foreach ($categories as $category => $config)
    @livewire('user-category-settings.category', ['model' => $user, 'category' => $category, 'config' => $config])
    @endforeach

</div>