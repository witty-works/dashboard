<div class="ibarra-sub-title-h1 margin-bottom">
    {{ __('guidelines.list_false_positives') }}
</div>

<div>
    <div class="py-10">
        @livewire('user-false-positive.form', ['user' => $user])
    </div>
</div>

<div>
    <div class="py-10">
        @livewire('user-false-positive.show', ['user' => $user])
    </div>
</div>

@if($user->currentTeam)
<div>
    <div class="py-10">
        @livewire('organization-false-positive.show', ['team' => $user->currentTeam, 'hide_actions' => true])
    </div>
</div>
@endif
