<div>
    <div class="py-10">
        @livewire('teams.store-context', ['team' => $team])
    </div>
</div>
            
<div>
    <div class="py-10">
        @livewire('organization-domain.type', ['team' => $team])
    </div>
</div>

<div>
    <div class="py-10">
        @livewire('organization-domain.form', ['team' => $team])
    </div>
</div>

<div>
    <div class="py-10">
        @livewire('organization-domain.show', ['team' => $team])
    </div>
</div>
