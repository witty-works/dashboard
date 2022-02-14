<div class="p-6 sm:px-20 bg-white border-b border-gray-200">
    <div>
        <x-jet-application-logo class="block h-12 w-auto" />
    </div>

    <div class="mt-8 text-2xl">
        <h1>{{ __('content.welcome')}}</h1>
    </div>

    <div class="mt-6 text-gray-500">
        {!! Str::markdown(__('content.welcome_text')) !!}
    </div>

    @auth
    @can('update', Auth::user()->currentTeam)
    <div class="mt-6 text-gray-500">
            <h2>{{ __('content.onboarding_next_steps') }}</h2>
            <ol>
                <li>{{ __('content.onboarding_create_team') }} @if (Auth::user()->currentTeam)✔️@endif</li>   
                <li>{{ __('content.onboarding_configure_organization_guidelines') }} @if (Auth::user()->currentTeam->organizationGuidelines)✔️@endif</li>   
                <li>{{ __('content.onboarding_invite_users') }} @if (Auth::user()->currentTeam && Auth::user()->currentTeam->users->count())✔️@endif</li>   
            </ol>
    </div>
    @endcan
    @endauth
</div>

<div class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-2">
    <div class="p-6">
        <div class="flex items-center">
            <img src="{{ asset('svg/witty-icon-color-line-inverted.svg') }}" width="30" />
            <div class="ml-4 text-lg text-gray-600 leading-7 font-semibold">
                {{ __('content.section_1_title') }}
            </div>
        </div>

        <div class="ml-12">
            <div class="mt-2 text-sm text-gray-500">
                {!! Str::markdown(__('content.section_1_text')) !!}
            </div>
        </div>
    </div>

    <div class="p-6 border-t border-gray-200 md:border-t-0 md:border-l">
        <div class="flex items-center">
            <img src="{{ asset('svg/039-computation-analysis.svg') }}" width="30" />
            <div class="ml-4 text-lg text-gray-600 leading-7 font-semibold">
                {{ __('content.section_2_title') }}
            </div>
        </div>

        <div class="ml-12">
            <div class="mt-2 text-sm text-gray-500">
                {!! Str::markdown(__('content.section_2_text')) !!}
            </div>
        </div>
    </div>

    <div class="p-6 border-t border-gray-200">
        <div class="flex items-center">
            <img src="{{ asset('svg/015-group-of-people.svg') }}" width="30" />
            <div class="ml-4 text-lg text-gray-600 leading-7 font-semibold">
                {{ __('content.section_3_title') }}
            </div>
        </div>

        <div class="ml-12">
            <div class="mt-2 text-sm text-gray-500">
                {!! Str::markdown(__('content.section_3_text')) !!}
            </div>
        </div>
    </div>

    <div class="p-6 border-t border-gray-200 md:border-l">
        <div class="flex items-center">
            <img src="{{ asset('svg/077-business-value.svg') }}" width="30" />
            <div class="ml-4 text-lg text-gray-600 leading-7 font-semibold">
                {{ __('content.section_4_title') }}
            </div>
        </div>

        <div class="ml-12">
            <div class="mt-2 text-sm text-gray-500">
                {!! Str::markdown(__('content.section_4_text')) !!}
            </div>
        </div>
    </div>
</div>
