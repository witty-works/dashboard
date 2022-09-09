<div>
    <div class="max-w-7xl mx-auto py-10">
        @livewire('user-term-replacement.form', ['user' => $user])
    </div>
</div>

<div>
    <div class="max-w-7xl mx-auto py-10">
        @livewire('user-term-replacement.show', ['user' => $user])
    </div>
</div>

@if($user->currentTeam)
<div>
    <div class="max-w-7xl mx-auto py-10">
        @livewire('organization-term-replacement.show', ['team' => $user->currentTeam, 'hide_actions' => true])
    </div>
</div>
@endif
