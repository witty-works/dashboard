<div class="ibarra-sub-title-h1 margin-top">
    {{ __('guidelines.dictionary_label') }}
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
