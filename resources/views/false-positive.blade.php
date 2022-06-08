@if (Auth::user()->hasTeamPermission($team, 'edit_guidelines'))
<div>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        @livewire('false-positive.form', ['team' => $team])
    </div>
</div>
@endif

<div>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        @livewire('false-positive.show', ['team' => $team])
    </div>
</div>
