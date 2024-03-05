<x-form-section class="py-10" submit="updateLanguageGuidelinesCategory" aria-label="{{ $config['translation']['name'] }}">

    <x-slot name="title">
        <div class="headline-row">
            <img width="60" class="category_icon" src="{{ $config['icon']['src'] }}" alt="{{ $config['translation']['name'] }} Icon"/>
            <span>{{ $config['translation']['name'] }}</span>
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

            $disabled = $proficiencyLevel === 'openly_discriminating';
            $title = $disabled ? __('guidelines.discriminating_language_cannot_be_disabled') : '';
        @endphp
        <div class="guidelines-form-section lato-small-text-p guidelines-form-section-proficiency-level" aria-expanded="false">
            <h3>{{ $proficiencyLevelData['translation']['hs_name'] }}</h3>

            @include('partials.toggle_label', ['disabled' => $disabled])

            @if(!empty($proficiencyLevelData['translation']['lead_text']))
            <a href="#" class="wittyworks-margin-left" onclick="
                document.getElementById('arrow-down-icon-{{ $category}}-{{ $proficiencyLevel }}').style.display == 'none' ? document.getElementById('arrow-down-icon-{{ $category}}-{{ $proficiencyLevel }}').style.display = 'block' : document.getElementById('arrow-down-icon-{{ $category}}-{{ $proficiencyLevel }}').style.display = 'none';
                document.getElementById('arrow-up-icon-{{ $category}}-{{ $proficiencyLevel }}').style.display == 'none' ? document.getElementById('arrow-up-icon-{{ $category}}-{{ $proficiencyLevel }}').style.display = 'block' : document.getElementById('arrow-up-icon-{{ $category}}-{{ $proficiencyLevel }}').style.display = 'none';
                document.getElementById('{{ $category}}-{{ $proficiencyLevel }}').style.display = (document.getElementById('{{ $category}}-{{ $proficiencyLevel }}').style.display === 'none' ? 'block' : 'none');
                return false;"
            >
                <img id="arrow-down-icon-{{ $category}}-{{ $proficiencyLevel }}" src="{{ asset('arrow-down-sign-to-navigate_small.svg') }}" alt="{{ __('content.open') }}" />
                <img id="arrow-up-icon-{{ $category}}-{{ $proficiencyLevel }}" src="{{ asset('arrow-up-sign-to-navigate_small.svg') }}" alt="{{ __('content.close') }}" style="display: none;" />
            </a>
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
        <div class="guidelines-form-section-ident lato-small-text-p">
            @php
                $label = '<a href="'.$diversityDimensionDrivers[$ddd]['translation']['canonical_url'].'" />';
                $label.= $diversityDimensionDrivers[$ddd]['translation']['hs_name'];
                $label.= '</a>';
                $label.= ' - '.$diversityDimensionDrivers[$ddd]['translation']['name'];
                if (!in_array(app()->getLocale(), $diversityDimensionDrivers[$ddd]['has_rules'])) {
                    $label.= ' ('.(app()->getLocale() === 'en' ? __('guidelines.german_only') : __('guidelines.english_only')).')';
                }
            @endphp

            @if(\App\Models\LanguageGuidelines::isBasicOnly($proficiencyLevel) || !$model->subscribed())
            <x-checkbox
                id="dimensions['{{$ddd}}']"
                name="dimensions_{{$ddd}}"
                value="1"
                :label="$label"
                wire:model="dimensions.{{$ddd}}"
                :enabled="$dimensions[$ddd]"
                :disabled="(bool)$disabled"
                :title="$title"
            />
            @else
            <x-triple-toggle
                id="dimensions['{{$ddd}}']"
                name="dimensions_{{$ddd}}"
                :value="$dimensions[$ddd]"
                :label="$label"
                wire:model="dimensions.{{$ddd}}"
                :disabled="(bool)$disabled"
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
        @if (Auth::user()->hasTeamPermission($model, 'edit_guidelines'))
        @include('partials/save_cancel_action')
        @endif
    </x-slot>

</x-form-section>
