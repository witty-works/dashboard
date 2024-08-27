<h1 class="ibarra-sub-title-h1 margin-top">
    {{ __('guidelines.dictionary_label') }}
</h1>

<div>
    <div class="py-10">
        @livewire('organization-term-replacement.form', ['model' => $team])
    </div>
</div>

<div>
    <div class="py-10">
        @livewire('organization-term-replacement.show', ['model' => $team])
    </div>
</div>
