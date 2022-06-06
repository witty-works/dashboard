<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('teams.language') }}
        </h2>

        <div class="mt-5">
        @foreach($tabs as $key => $route)
        <a href="{{ route($route) }}" class="rounded-t-md border px-4 lg:px-6 tab-navigation-link{{ $key === $tab ? ' tab-navigation-link-active' : ''}} color:black">
            {{ __("guidelines.{$key}_label") }}
        </a>
        @endforeach
        </div>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($team)
            <div class="w-full mb-14">
                <div class="p-6 guidelines-tagline">
                    @if($tab === \App\Http\Controllers\Livewire\GuidelinesController::CUSTOMIZE_WITTY)
                    @if (!Auth::user()->hasTeamPermission($team, 'edit_guidelines'))
                        {!! __('content.making_changes_requires_admin_rights', ['email' => $team->owner->email]) !!}
                    @else
                        {{ __('guidelines.manage_organization_guidelines_description') }}
                    @endif
                    @elseif($tab === \App\Http\Controllers\Livewire\GuidelinesController::TERM_REPLACEMENTS)
                        {!! Str::markdown(__('guidelines.create_new_term_replacement_description')) !!}
                    @if($team->getFalsePositivesLimitReached() && Auth::user()->ownsTeam($team) && !$team->subscribed())
                        {!! __('guidelines.term_replacement_limit_reached', ['max_count' => $team->getTermReplacementsCount(), 'url' => route('stripe.portal')]) !!}
                    @endif
                    @elseif($tab === \App\Http\Controllers\Livewire\GuidelinesController::FALSE_POSITIVES)
                        {!! Str::markdown(__('guidelines.create_new_false_positive_description')) !!}

                        @if($team->getFalsePositivesLimitReached() && Auth::user()->ownsTeam($team) && !$team->subscribed())
                            {!! __('guidelines.false_positive_limit_reached', ['max_count' => $team->getFalsePositivesCount(), 'url' => route('stripe.portal')]) !!}
                        @endif
                    @endif

                    <div class="text-body-color text-base leading-relaxed">
                        @if($tab === \App\Http\Controllers\Livewire\GuidelinesController::CUSTOMIZE_WITTY)
                            @include('organization-guidelines')
                        @elseif($tab === \App\Http\Controllers\Livewire\GuidelinesController::TERM_REPLACEMENTS)
                            @include('term-replacement')
                        @elseif($tab === \App\Http\Controllers\Livewire\GuidelinesController::FALSE_POSITIVES)
                            @include('false-positive')
                        @endif
                    </div>
                </div>
            </div>
            @else
            @include('partials.onboarding')
            @endif
        </div>
    </div>
</x-app-layout>
