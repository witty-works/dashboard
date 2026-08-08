<div>
<div class="pt-10" role="group" aria-labelledby="advancedToggleTitle">
    <x-section-title>
        <x-slot name="title" class="ibarra-sub-title-h2">
            <span id="advancedToggleTitle">{{ __('guidelines.advanced_toggle_title') }}</span>
        </x-slot>

        <x-slot name="description" class="lato-paragraph-text-p">
            <div class="headline-row container border-radius" role="group" aria-labelledby="toggleDescription">
                <span id="toggleDescription">{!! __('guidelines.advanced_toggle_description') !!}</span>

                <div class="py-3 container-row m-5" aria-hidden="true">
                    <button id="off" disabled="disabled" tabindex="-1" class="tripple-toggle" aria-label="{{ __('guidelines.proficiency_level_off') }}"></button>
                    <span id="label-off" class="cursor-pointer">{{ __('guidelines.proficiency_level_off') }}</span>

                    <button id="basic" disabled="disabled" tabindex="-1" class="tripple-toggle active middle wittyworks-margin-left" aria-label="{{ __('guidelines.proficiency_level_basic') }}"></button>
                    <span id="label-basic" class="cursor-pointer">{{ __('guidelines.proficiency_level_basic') }}</span>

                    <button id="advanced" disabled="disabled" tabindex="-1" class="tripple-toggle active wittyworks-margin-left" aria-label="{{ __('guidelines.proficiency_level_advanced') }}"></button>
                    <span id="label-advanced" class="cursor-pointer">{{ __('guidelines.proficiency_level_advanced') }}</span>
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
</div>