@if (Auth::user()->hasTeamPermission($team, 'edit_guidelines'))
<div>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        @livewire('term-replacement.form', ['team' => $team])
    </div>
</div>
@endif

<div>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        @livewire('term-replacement.show', ['team' => $team])
    </div>
</div>
