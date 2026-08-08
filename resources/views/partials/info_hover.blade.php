<button type="button" class="m-3 info-hover-trigger"
    aria-expanded="false"
    aria-controls="{{ $category }}-image"
    onmouseover="infoHoverShow(this)"
    onfocus="infoHoverShow(this)"
    onclick="infoHoverShow(this)"
    onmouseout="infoHoverScheduleHide()"
    onblur="infoHoverScheduleHide()">
    <img width="15" src="{{ asset('information-icon.svg') }}" alt="" aria-hidden="true" />
    <span class="sr-only">{{ __('content.example') }}: {{ $name }}</span>
</button>
<div id="{{ $category }}-image" class="information-image" role="tooltip"
    onmouseover="infoHoverCancelHide()"
    onmouseout="infoHoverScheduleHide()">
    <div class="information-title">{{ __('content.example') }}: {{ $name }}</div>

    @if(!empty($text))
    <div class="information-text">{!! $text !!}</div>
    @else
    <div class="flex-container">
        <div class="information-text">
            @if(!empty($config['translation']['example_image_advanced']['src']))
            {{ __('guidelines.proficiency_level_basic') }}
            @endif
            <img src="{{ $config['translation']['example_image']['src'] }}" alt="{{ $config['translation']['example_image']['alt'] }}" width="400" />
        </div>
        @if(!empty($config['translation']['example_image_advanced']['src']))
        <div class="information-text">
            @if(!empty($config['translation']['example_image']['src']))
            {{ __('guidelines.proficiency_level_advanced') }}
            @endif
            <img src="{{ $config['translation']['example_image_advanced']['src'] }}" alt="{{ $config['translation']['example_image_advanced']['alt'] }}" width="400" />
        </div>
        @endif
    </div>
    @endif
</div>
