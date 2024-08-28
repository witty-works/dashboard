@php
$pagetitle = __('guidelines.language');
switch ($tab) {
    case \App\Http\Controllers\Livewire\UserGuidelinesController::CATEGORY_SETTINGS:
        $pagetitle = __('guidelines.language');
        break;
    case \App\Http\Controllers\Livewire\UserGuidelinesController::LANGUAGE_SETTINGS:
        $pagetitle = __('guidelines.language_settings_label');
        break;
    case \App\Http\Controllers\Livewire\UserGuidelinesController::TERM_REPLACEMENTS:
        $pagetitle = __('guidelines.dictionary_label');
        break;
    case \App\Http\Controllers\Livewire\UserGuidelinesController::FALSE_POSITIVES:
        $pagetitle = __('guidelines.ignore_words_label');
        break;
    case \App\Http\Controllers\Livewire\UserGuidelinesController::DOMAINS:
        $pagetitle = __('guidelines.privacy_settings_label');
        break;
}
@endphp
<x-app-layout :pagetitle="$pagetitle">
    <nav class="wittyworks-navigation-wrapper" id="maincontent" aria-label="Main Navigation">
    @livewire('navigation-menu')
    </nav>
    <div class="wittyworks-page-wrapper">
        <div class="wittyworks-page lg:ml-20">
            @include('partials.banners')
            @if($team)
                @if($tab === \App\Http\Controllers\Livewire\OrganizationGuidelinesController::CATEGORY_SETTINGS)
                    @include('teams/category-settings')
                @elseif($tab === \App\Http\Controllers\Livewire\OrganizationGuidelinesController::LANGUAGE_SETTINGS)
                    @include('teams/language-settings')
                @elseif($tab === \App\Http\Controllers\Livewire\OrganizationGuidelinesController::TERM_REPLACEMENTS)
                    @include('teams/term-replacement')
                @elseif($tab === \App\Http\Controllers\Livewire\OrganizationGuidelinesController::FALSE_POSITIVES)
                    @include('teams/false-positive')
                @elseif($tab === \App\Http\Controllers\Livewire\OrganizationGuidelinesController::DOMAINS)
                    @include('teams/domain')
                @endif
            @else
            @include('partials.invitations')
            @endif
        </div>
    </div>
</x-app-layout>
