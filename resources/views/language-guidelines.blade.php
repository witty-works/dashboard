@php
$user = Auth::user();
$currentTeam = $user->currentTeam;
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('content.language_guidelines') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div>
                <div class="onboarding-container--two-col">
                    <div class="onboarding-container-col-one">
                        <div class="onboarding-title">{{ __('content.onboarding_quickLinks') }}</div>
                        <div class="onboarding-quick-links-container">
                            @if($currentTeam)
                            <a class="onboarding-iconWrapper" href="{{ route('organization-guidelines', $currentTeam->id) }}">
                                <img src="{{ url('svg/language-guidelines.svg') }}" alt="{{ __('content.onboarding_language_guidelines') }}"/>
                                <div class="onboarding-icon-description">{{ __('guidelines.organization_guidelines') }}</div>
                            </a>
                            <a class="onboarding-iconWrapper" href="{{ route('term-replacement', $currentTeam->id) }}">
                                <img src="{{ url('svg/language-guidelines.svg') }}" alt="{{ __('content.onboarding_language_guidelines') }}s"/>
                                <div class="onboarding-icon-description">{{ __('guidelines.term_replacement_list') }}</div>
                            </a>
                            <a class="onboarding-iconWrapper" href="{{ route('false-positive', $currentTeam->id) }}">
                                <img src="{{ url('svg/language-guidelines.svg') }}" alt="{{ __('content.onboarding_language_guidelines') }}"/>
                                <div class="onboarding-icon-description">{{ __('guidelines.false_positive_list') }}</div>
                            </a>
                            @endif
                            <a class="onboarding-iconWrapper" href="https://www.witty.works/help">
                                <img src="{{ url('svg/support.svg') }}" alt="{{ __('content.onboarding_support') }}"/>
                                <div class="onboarding-icon-description">{{ __('content.onboarding_support') }}</div>
                            </a>
                        </div>
                    </div>
                    <div class="onboarding-container-col-two">
                        <div class="onboarding-title">{{ __('content.introduction_videos') }}</div>

                        <div class="pt-6">
                            <x-embed url="https://www.youtube.com/watch?v=P4Qy6RrqBYQ" />
                       </div>

                        <div class="pt-6">
                            <x-embed url="https://www.youtube.com/watch?v=giQrWB9C7Xg" />
                        </div>

                        <div class="pt-6">
                            <x-embed url="https://www.youtube.com/watch?v=L7TXSB3Me-8" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
