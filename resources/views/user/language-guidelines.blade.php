@php
$pagetitle = __('teams.language');
switch ($tab) {
    case \App\Http\Controllers\Livewire\UserGuidelinesController::CUSTOMIZE_WITTY:
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
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
        <div class="wittyworks-page-wrapper">
            <div class="wittyworks-page lg:ml-20">
                @include('partials.banners')
                @if($tab === \App\Http\Controllers\Livewire\UserGuidelinesController::CUSTOMIZE_WITTY)
                    @include('user/user-guidelines')
                @elseif($tab === \App\Http\Controllers\Livewire\UserGuidelinesController::TERM_REPLACEMENTS)
                    @include('user/term-replacement')
                @elseif($tab === \App\Http\Controllers\Livewire\UserGuidelinesController::FALSE_POSITIVES)
                    @include('user/false-positive')
                @elseif($tab === \App\Http\Controllers\Livewire\UserGuidelinesController::DOMAINS)
                    @include('user/domain')
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
