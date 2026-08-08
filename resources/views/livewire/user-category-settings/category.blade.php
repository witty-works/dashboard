<x-form-section class="py-10" submit="updateLanguageGuidelinesCategory">
    <x-slot name="title">
        <div class="headline-row">
            <img width="60" class="category_icon" src="{{ $config['icon']['src'] }}" alt=""/>
            {{ $config['translation']['name'] }}
            @if(!empty($config['translation']['example_image']['src']))
            @include('partials.info_hover', ['category' => $category, 'name' => $config['translation']['name'], 'config' => $config])
            @endif
        </div>
    </x-slot>

    <x-slot name="description">
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesCategory">
        <p>
            {!! $config['translation']['lead_title'] !!}
            <a href="{{ $config['translation']['canonical_url']}}" target="_blank" rel="noopener noreferrer">{{ __('content.learn_more') }}</a>
        </p>
        @foreach ($proficiencyLevels as $proficiencyLevel => $proficiencyLevelData)
        @php
            $list = $config['diversity_dimension_drivers'][$proficiencyLevel] ?? [];
            if (empty($list)) {
                continue;
            }

            $callback = function ($a, $b) use ($diversityDimensionDrivers) {
                if (empty($diversityDimensionDrivers[$a]['translation']['hs_name'])) {
                    return 0;
                }

                if (empty($diversityDimensionDrivers[$b]['translation']['hs_name'])) {
                    return 0;
                }

                if ($diversityDimensionDrivers[$a]['translation']['hs_name'] == $diversityDimensionDrivers[$b]['translation']['hs_name']) {
                    return 0;
                }

                return ($diversityDimensionDrivers[$a]['translation']['hs_name'] < $diversityDimensionDrivers[$b]['translation']['hs_name']) ? -1 : 1;
            };
            usort($list, $callback);

            if ($proficiencyLevel === 'openly_discriminating') {
                $disabled = true;
            } else {
                $force = \App\Models\LanguageGuidelines::isForcedOnTeam($model, 'disabled_categories', $category);
                if ($force) {
                    $disabled = 'locked';
                } else {
                    $disabled = false;
                }
            }
        @endphp

        <div class="guidelines-form-section lato-small-text-p guidelines-form-section-proficiency-level">
            <h3>{{ $proficiencyLevelData['translation']['hs_name'] }}</h3>

            @include('partials.toggle_label', ['disabled' => $disabled])

            @if(!empty($proficiencyLevelData['translation']['lead_text']))
            <button type="button" class="wittyworks-margin-left" aria-expanded="false" aria-controls="{{ $category }}-{{ $proficiencyLevel }}" onclick="
                document.getElementById('arrow-down-icon-{{ $category}}-{{ $proficiencyLevel }}').style.display == 'none' ? document.getElementById('arrow-down-icon-{{ $category}}-{{ $proficiencyLevel }}').style.display = 'block' : document.getElementById('arrow-down-icon-{{ $category}}-{{ $proficiencyLevel }}').style.display = 'none';
                document.getElementById('arrow-up-icon-{{ $category}}-{{ $proficiencyLevel }}').style.display == 'none' ? document.getElementById('arrow-up-icon-{{ $category}}-{{ $proficiencyLevel }}').style.display = 'block' : document.getElementById('arrow-up-icon-{{ $category}}-{{ $proficiencyLevel }}').style.display = 'none';
                document.getElementById('{{ $category}}-{{ $proficiencyLevel }}').style.display = (document.getElementById('{{ $category}}-{{ $proficiencyLevel }}').style.display === 'none' ? 'block' : 'none');
                this.setAttribute('aria-expanded', this.getAttribute('aria-expanded') === 'true' ? 'false' : 'true');"
            >
                <span class="sr-only">{{ $proficiencyLevelData['translation']['hs_name'] }}</span>
                <img id="arrow-down-icon-{{ $category}}-{{ $proficiencyLevel }}" src="{{ asset('arrow-down-sign-to-navigate_small.svg') }}" alt="" aria-hidden="true" />
                <img id="arrow-up-icon-{{ $category}}-{{ $proficiencyLevel }}" src="{{ asset('arrow-up-sign-to-navigate_small.svg') }}" alt="" aria-hidden="true" style="display: none;" />
            </button>
            @endif
        </div>

        <x-input-error for="dimensions" class="mt-2" />

        @if(!empty($proficiencyLevelData['translation']['lead_text']))
        <div id="{{ $category }}-{{ $proficiencyLevel }}" style="display: none" class="proficiency-level-p">
            {!! $proficiencyLevelData['translation']['lead_text'] !!}
        </div>
        @endif

        @foreach ($list as $ddd)
        @if(isset($diversityDimensionDrivers[$ddd]['translation']) && isset($dimensions[$ddd]))
        @php
        $checkbox = \App\Models\LanguageGuidelines::isBasicOnly($proficiencyLevel);
        if ($proficiencyLevel === 'openly_discriminating') {
            $minValue = \App\Models\LanguageGuidelines::BASIC_ENABLED;
            $title = __('guidelines.discriminating_language_cannot_be_disabled');
        } else {
            $minValue = \App\Models\LanguageGuidelines::teamCategoryValue($model, $ddd, $proficiencyLevel);
            $title = $disabled ? __('content.customize_via_team_settings') : '';
        }

        @endphp

        <div class="guidelines-form-section-ident lato-small-text-p">
            @php
                $labelSuffix = ' - '.$diversityDimensionDrivers[$ddd]['translation']['short_explanation'];
                if ($diversityDimensionDrivers[$ddd]['has_rules'] === ['en']) {
                    $labelSuffix.= ' ('.__('guidelines.english_only').')';
                } elseif ($diversityDimensionDrivers[$ddd]['has_rules'] === ['de']) {
                    $labelSuffix.= ' ('.__('guidelines.german_only').')';
                } elseif ($diversityDimensionDrivers[$ddd]['has_rules'] === ['fr']) {
                    $labelSuffix.= ' ('.__('guidelines.french_only').')';
                } elseif (!in_array(app()->getLocale(), $diversityDimensionDrivers[$ddd]['has_rules'])) {
                    $labelSuffix.= ' ('.__('guidelines.other_language').')';
                }
                $checkboxLabel = $diversityDimensionDrivers[$ddd]['translation']['hs_name'].$labelSuffix;
                $label = '<a href="'.$diversityDimensionDrivers[$ddd]['translation']['canonical_url'].'" />';
                $label.= $diversityDimensionDrivers[$ddd]['translation']['hs_name'];
                $label.= '</a>';
                $label.= $labelSuffix;
            @endphp

            @if($checkbox)
            <x-checkbox
                id="dimensions['{{$ddd}}']"
                name="dimensions_{{$ddd}}"
                value="1"
                :label="$checkboxLabel"
                wire:model="dimensions.{{$ddd}}"
                :enabled="$dimensions[$ddd] || $minValue === 1"
                :disabled="(bool)$disabled || $minValue === 1"
                :title="$title"
            />
            <a href="{{ $diversityDimensionDrivers[$ddd]['translation']['canonical_url'] }}">{{ __('content.learn_more') }}</a>
            @else
            <x-triple-toggle
                id="dimensions['{{$ddd}}']"
                name="dimensions_{{$ddd}}"
                :value="$dimensions[$ddd]"
                :label="$label"
                wire:model="dimensions.{{$ddd}}"
                :disabled="(bool)$disabled || $minValue === 2"
                :minValue="$minValue"
            />
            @endif

            @if($proficiencyLevel !== 'openly_discriminating' && !empty($diversityDimensionDrivers[$ddd]['translation']['example_image']['src']))
            @include('partials.info_hover', ['category' => $ddd, 'name' => $diversityDimensionDrivers[$ddd]['translation']['hs_name'], 'config' => $diversityDimensionDrivers[$ddd]])
            @endif
        </div>
        <x-input-error for="dimensions" class="mt-2" />
        @endif
        @endforeach
        @endforeach
    </x-slot>

    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>

</x-form-section>
