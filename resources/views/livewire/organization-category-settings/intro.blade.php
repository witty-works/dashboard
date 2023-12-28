@if($model->subscribed())
<div class="pt-10" aria-labelledby="advancedToggleTitle">
    <x-section-title>
        <x-slot name="title" class="ibarra-sub-title-h2">
            <span id="advancedToggleTitle">{{ __('guidelines.advanced_toggle_title') }}</span>
        </x-slot>

        <x-slot name="description" class="lato-paragraph-text-p">
            <div class="headline-row container border-radius" aria-labelledby="toggleDescription">
                <span id="toggleDescription">{!! __('guidelines.advanced_toggle_description') !!}</span>

                <div class="py-3 container-row m-5">
                    <button id="off" disabled="disabled" class="tripple-toggle"></button>
                    <label for="off">{{ __('guidelines.proficiency_level_off') }}</label>
                    
                    <button id="basic" disabled="disabled" class="tripple-toggle active middle wittyworks-margin-left"></button>
                    <label for="basic">{{ __('guidelines.proficiency_level_basic') }}</label>
                    
                    <button id="advanced" disabled="disabled" class="tripple-toggle active wittyworks-margin-left"></button>
                    <label for="advanced">{{ __('guidelines.proficiency_level_advanced') }}</label>
                </div>

                <div class="guidelines-form-section-ident lato-small-text-p">
                    {!! __('guidelines.advanced_toggle_advice') !!}
                    @include('partials.info_hover', ['category' => 'foo', 'name' => __('guidelines.toggle_advanced_vs'), 'text' => __('guidelines.advanced_toggle_example')])
                </div>

                <div class="guidelines-form-section-ident lato-small-text-p">
                    {!! __('guidelines.advanced_toggle_disclaimer') !!}
                </div>
            </div>
        </x-slot>
    </x-section-title>
</div>
@endif
