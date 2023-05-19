@if($model->subscribed())
<div class="pt-10">
    <x-jet-section-title>
        <x-slot name="title" class="ibarra-sub-title-h2">
            {{ __('guidelines.advanced_toggle_title') }}
        </x-slot>

        <x-slot name="description" class="lato-paragraph-text-p">
            <div class="headline-row">
                {!! __('guidelines.advanced_toggle_description') !!}

                <div class="py-3 container-row" style="align-items: center; !important">
                    <div class="tripple-toggle"></div>{{ __('guidelines.proficiency_level_off') }}
                    <div class="tripple-toggle active middle wittyworks-margin-left"></div>{{ __('guidelines.proficiency_level_basic') }}
                    <div class="tripple-toggle active wittyworks-margin-left"></div>{{ __('guidelines.proficiency_level_advanced') }}
                </div>

                {!! __('guidelines.advanced_toggle_disclaimer') !!}
                @include('partials.info_hover', ['category' => 'foo', 'name' => __('guidelines.toggle_advanced_vs'), 'text' => __('guidelines.advanced_toggle_example')])
            </div>
        </x-slot>
    </x-jet-section-title>
</div>
@endif
