@props(['id' => null])

@php
    $sectionId = $id ?? 'section-'.substr(md5((string) $title.(string) ($description ?? '')), 0, 8);
@endphp

<div>
    <div>
        <h2 class="ibarra-sub-title-h2" id="{{ $sectionId }}-title">{{ $title }}</h2>
        <div class="lato-paragraph-text-p margin-bottom" id="{{ $sectionId }}-description">{{ $description }}</div>
    </div>

    <div>
        {{ $aside ?? '' }}
    </div>
</div>
