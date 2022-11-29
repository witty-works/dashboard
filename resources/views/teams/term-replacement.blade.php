<div class="ibarra-sub-title-h1 margin-top">
    {{ __('guidelines.list_false_positives') }}
</div>

<div>
    <div class="py-10">
        @livewire('organization-term-replacement.form', ['team' => $team])
    </div>
</div>

<div>
    <div class="py-10">
        @livewire('organization-term-replacement.show', ['team' => $team])
    </div>
</div>
