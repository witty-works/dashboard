@props(['id' => null])

@php
    $sectionId = $id ?? 'section-'.substr(md5((string) $title.(string) ($description ?? '')), 0, 8);
@endphp

<div>
    <div>
        {{-- A list without entries has no title; an empty heading would still be announced. --}}
        @if (isset($title) && trim((string) $title) !== '')
        <h2 class="ibarra-sub-title-h2" id="{{ $sectionId }}-title">{{ $title }}</h2>
        @endif
        <div class="lato-paragraph-text-p margin-bottom" id="{{ $sectionId }}-description">{{ $description }}</div>
    </div>

    <div>
        {{ $aside ?? '' }}
    </div>
</div>
