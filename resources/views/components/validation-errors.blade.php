@if ($errors->any())
    <div {{ $attributes }}>
        <div class="font-medium text-red-700">{{ __('content.woops') }}</div>

        <ul class="mt-3 list-disc list-inside text-sm text-red-700">
            {{-- Each field's first message carries the id its field points at (aria-describedby). --}}
            @foreach ($errors->getMessages() as $field => $messages)
                @foreach ($messages as $error)
                    <li @if ($loop->first) id="{{ str_replace('.', '_', $field) }}-error" @endif>{{ $error }}</li>
                @endforeach
            @endforeach
        </ul>
    </div>
@endif
