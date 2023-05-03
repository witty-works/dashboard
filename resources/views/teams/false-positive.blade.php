<div class="ibarra-sub-title-h1 margin-top">
    {{ __('guidelines.false_positives_label') }}
</div>

<div>
    <div class="py-10">
        @livewire('organization-false-positive.form', ['model' => $team])
    </div>
</div>

<div>
    <div class="py-10">
        @livewire('organization-false-positive.show', ['model' => $team])
    </div>
</div>
