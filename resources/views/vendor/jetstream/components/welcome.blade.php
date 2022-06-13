<div class="p-6 sm:px-20 bg-white border-b border-gray-200">
    <div class="text-2xl">
        <h1>{{ __('content.welcome')}}</h1>
    </div>
</div>

<div class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-2">
    <div class="p-6">
        <div class="flex items-center">
            <img src="{{ asset('svg/witty-icon-color-line-inverted.svg') }}" width="30" />
            <div class="ml-4 text-lg leading-7 font-semibold">
                {{ __('content.section_1_title') }}
            </div>
        </div>

        <div class="ml-12">
            <div class="mt-2 text-sm">
                {!! Str::markdown(__('content.section_1_text')) !!}
            </div>
        </div>
    </div>

    <div class="p-6 border-t border-gray-200 md:border-t-0 md:border-l">
        <div class="flex items-center">
            <img src="{{ asset('svg/039-computation-analysis.svg') }}" width="30" />
            <div class="ml-4 text-lg leading-7 font-semibold">
                {{ __('content.section_2_title') }}
            </div>
        </div>

        <div class="ml-12">
            <div class="mt-2 text-sm">
                {!! Str::markdown(__('content.section_2_text')) !!}

                @guest
                {!! __('content.login_cta', ['url' => route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'register'])]) !!}
                @endguest
            </div>
        </div>
    </div>

    <div class="p-6 border-t border-gray-200">
        <div class="flex items-center">
            <img src="{{ asset('svg/015-group-of-people.svg') }}" width="30" />
            <div class="ml-4 text-lg leading-7 font-semibold">
                {{ __('content.section_3_title') }}
            </div>
        </div>

        <div class="ml-12">
            <div class="mt-2 text-sm">
                {!! Str::markdown(__('content.section_3_text')) !!}
            </div>
        </div>
    </div>

    <div class="p-6 border-t border-gray-200 md:border-l">
        <div class="flex items-center">
            <img src="{{ asset('svg/077-business-value.svg') }}" width="30" />
            <div class="ml-4 text-lg leading-7 font-semibold">
                {{ __('content.section_4_title') }}
            </div>
        </div>

        <div class="ml-12">
            <div class="mt-2 text-sm">
                {!! Str::markdown(__('content.section_4_text')) !!}
            </div>
        </div>
    </div>
</div>
