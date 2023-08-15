@if(\App\Models\LanguageGuidelines::doUserTeamSettingsDiffer($model, 'language-settings'))
<div class="pt-10" aria-labelledby="resetTitle">
    <x-jet-section-title>
        <x-slot name="title" class="ibarra-sub-title-h2">
            <span id="resetTitle">{{ __('guidelines.reset_to_team_suggestions_title') }}</span>
        </x-slot>

        <x-slot name="description" class="lato-paragraph-text-p">
            <div class="container border-radius" aria-labelledby="resetDescription">
                <span id="resetDescription">{!! __('guidelines.reset_to_team_suggestions_description') !!}</span>

                <form action="{{ route('user.language-settings-reset') }}" aria-label="{{ __('guidelines.form_reset_language_aria_label') }}">
                    <x-jet-button>
                        {{ __('guidelines.reset_to_team') }}
                    </x-jet-button>
                </form>
            </div>
        </x-slot>
    </x-jet-section-title>
</div>
@endif
