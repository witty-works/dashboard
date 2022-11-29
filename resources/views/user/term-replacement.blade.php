<div class="ibarra-sub-title-h1 margin-top">
    {{ __('guidelines.dictionary_label') }}
</div>

<div>
    <div class="py-10">
        @livewire('user-term-replacement.form', ['user' => $user])
    </div>
</div>

<div>
    <div class="py-10">
        @livewire('user-term-replacement.show', ['user' => $user])
    </div>
</div>

@if($user->currentTeam)
<div>
    <div class="py-10">
        @livewire('organization-term-replacement.show', ['team' => $user->currentTeam, 'hide_actions' => true])
    </div>
</div>
@endif
