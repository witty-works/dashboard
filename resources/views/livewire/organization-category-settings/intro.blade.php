@if($model->subscribed())
<div class="pt-10" aria-labelledby="advancedToggleTitle">
    <x-jet-section-title>
        <x-slot name="title" class="ibarra-sub-title-h2">
            <span id="advancedToggleTitle">{{ __('guidelines.advanced_toggle_title') }}</span>
        </x-slot>

        <x-slot name="description" class="lato-paragraph-text-p">
            <div class="headline-row container border-radius" aria-labelledby="toggleDescription">
                <span id="toggleDescription">{!! __('guidelines.advanced_toggle_description') !!}</span>

                <div class="py-3 container-row" style="align-items: center;">
                    <button class="tripple-toggle" aria-label="{{ __('guidelines.proficiency_toggle_off_aria_label') }}"></button>
                    <span>{{ __('guidelines.proficiency_level_off') }}</span>
                    
                    <button class="tripple-toggle active middle wittyworks-margin-left" aria-label="{{ __('guidelines.proficiency_toggle_basic_aria_label') }}"></button>
                    <span>{{ __('guidelines.proficiency_level_basic') }}</span>
                    
                    <button class="tripple-toggle active wittyworks-margin-left" aria-label="{{ __('guidelines.proficiency_toggle_advanced_aria_label') }}"></button>
                    <span>{{ __('guidelines.proficiency_level_advanced') }}</span>
                </div>

                {!! __('guidelines.advanced_toggle_disclaimer') !!}
                @include('partials.info_hover', ['category' => 'foo', 'name' => __('guidelines.toggle_advanced_vs'), 'text' => __('guidelines.advanced_toggle_example')])
            </div>
        </x-slot>
    </x-jet-section-title>
</div>
@endif
