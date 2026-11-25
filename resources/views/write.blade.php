<x-app-layout :pagetitle="__('content.write_title')">
    <div class="wittyworks-navigation-wrapper">
    @livewire('navigation-menu')
    </div>
    <div class="wittyworks-page-wrapper" id="maincontent">
        <div class="wittyworks-page lg:ml-20">
            @include('partials.banners')

            <h1 class="text-2xl">{{ __('content.write_title') }}</h1>
            <p class="mt-2">{{ __('content.write_intro') }}</p>

            @if (! $configured)
                <p class="mt-4" role="status">{{ __('content.write_not_configured') }}</p>
            @else
                {{-- Read by resources/js/write.js. Encoded so no value can end the element. --}}
                <script type="application/json" id="write-config">@json($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)</script>
                <script type="application/json" id="write-messages">@json($messages, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)</script>

                <p id="write-status" class="mt-4" role="status" aria-live="polite"></p>

                <div id="write-editor" class="mt-4"></div>

                <form id="write-prompt" class="mt-4" hidden>
                    <label for="write-prompt-text">{{ __('content.write_prompt') }}</label>
                    <textarea id="write-prompt-text" class="mt-1 block w-full" rows="2"></textarea>
                    <label class="mt-2 inline-flex items-center">
                        <input type="checkbox" id="write-ai">
                        <span class="ml-2">{{ __('content.write_ai_suggestions') }}</span>
                    </label>
                    <button type="submit" class="mt-2">{{ __('content.write_send') }}</button>
                </form>

                <div id="write-issues" class="mt-4"></div>

                @vite('resources/js/write.js')
            @endif
        </div>
    </div>
</x-app-layout>
