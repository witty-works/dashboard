@if($model->subscribed())
<div class="pt-10">
    <x-jet-section-title>
        <x-slot name="title" class="ibarra-sub-title-h2">
            {{ __('guidelines.advanced_toggle_title') }}
        </x-slot>

        <x-slot name="description" class="lato-paragraph-text-p">
            {!! __('guidelines.advanced_toggle_description') !!}
        </x-slot>
    </x-jet-section-title>
</div>
@endif
