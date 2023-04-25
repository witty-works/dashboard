<x-jet-form-section class="py-10" submit="updateLanguageGuidelinesCategory">
    <x-slot name="title">
        <div class="flex">
            <img src="{{ $config['icon']['src'] }}" alt="{{ $config['translation']['name'] }} Icon" width="36" class="padding: 5px;" />
            {{ $config['translation']['name'] }}
            @if(!empty($config['translation']['example_image']['src']))
            <span class="ml-3" onmouseover="document.getElementById('{{ $category }}-image').style.display = 'block';" onmouseout="document.getElementById('{{ $category }}-image').style.display = 'none';">
                (i)
            </span>
            @endif
        </div>
    </x-slot>

    <x-slot name="description">
        @if(!empty($config['translation']['example_image']['src']))
        <img id="{{ $category }}-image" style="display: none;" src="{{ $config['translation']['example_image']['src'] }}" alt="{{ $config['translation']['example_image']['alt'] }}">
        @endif
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesCategory">
        <p>
            {!! $config['translation']['lead_title'] !!}
            <a href="{{ $config['translation']['canonical_url']}}" target=”_blank” rel=”noopener”>{{ __('content.learn_more') }}</a>
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
                $upgrade = $proficiencyLevel !== 'unconscious_bias' && !$model->subscribed();

                if ($upgrade) {
                    $disabled = 'upgrade';
                } else {
                    $disabled = false;
                }
            }
        @endphp
        <div class="guidelines-form-section lato-small-text-p">
            {{ $proficiencyLevelData['translation']['hs_name'] }}

            @include('partials.toggle_label', ['disabled' => $disabled])

            @if(!empty($proficiencyLevelData['translation']['lead_text']))
            &nbsp;<a href="javascript: return false;" onclick="document.getElementById('{{ $category}}-{{ $proficiencyLevel }}').style.display = (document.getElementById('{{ $category}}-{{ $proficiencyLevel }}').style.display === 'none' ? 'block' : 'none');">
                <img src="{{ asset('arrow-down-sign-to-navigate_small.png') }}" />
            </a>
            @endif
        </div>
        <x-jet-input-error for="dimensions" class="mt-2" />
        @if(!empty($proficiencyLevelData['translation']['lead_text']))
        <div id="{{ $category }}-{{ $proficiencyLevel }}" style="display: none" class="lato-paragraph-text-p">
            {!! $proficiencyLevelData['translation']['lead_text'] !!}
        </div>
        @endif
        @foreach ($list as $ddd)
        @if(isset($diversityDimensionDrivers[$ddd]['translation']))
        <div class="guidelines-form-section-ident lato-small-text-p">
            @php
                $label = '<a href="'.$diversityDimensionDrivers[$ddd]['translation']['canonical_url'].'" />';
                $label.= $diversityDimensionDrivers[$ddd]['translation']['hs_name'];
                $label.= '</a>';
                $label.= ' - '.$diversityDimensionDrivers[$ddd]['translation']['name'];
            @endphp

            @if($proficiencyLevel === 'openly_discriminating' || !$model->subscribed())
            <x-jet-checkbox
                id="dimensions['{{$ddd}}']"
                value="{{ $dimensions[$ddd] }}"
                :label="$label"
                wire:model.defer="dimensions.{{$ddd}}"
                :disabled="(bool)$disabled"
            />
            @else
            <x-triple-toggle
                id="dimensions['{{$ddd}}']"
                name="dimensions_{{$ddd}}"
                value="1"
                :label="$label"
                wire:model.defer="dimensions.{{$ddd}}"
                :disabled="(bool)$disabled"
            />
            @endif
            
            @if(!empty($diversityDimensionDrivers[$ddd]['translation']['example_image']['src']))
            <div class="m-3" onmouseover="document.getElementById('{{ $ddd }}-image').style.display = 'block';" onmouseout="document.getElementById('{{ $ddd }}-image').style.display = 'none';">
                (i)
            </div>
            <img id="{{ $ddd }}-image" style="display: none;" src="{{ $diversityDimensionDrivers[$ddd]['translation']['example_image']['src'] }}" alt="{{ $diversityDimensionDrivers[$ddd]['translation']['example_image']['alt'] }}">
            @endif
        </div>
        <x-jet-input-error for="dimensions" class="mt-2" />
        @endif
        @endforeach
        @endforeach
    </x-slot>

    <x-slot name="actions">
        <div class="guidelines-form-section--apply-for-all">
            <x-jet-checkbox
                id="dimensions_force"
                value="1"
                :label="__('guidelines.set_for_all')"
                wire:model.defer="dimensions_force"
                :disabled="!$model->subscribed()"
            />
        </div>

        @if (Auth::user()->hasTeamPermission($model, 'edit_guidelines'))
        @include('partials/save_cancel_action')
        @endif
    </x-slot>

</x-jet-form-section>
