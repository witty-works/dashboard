@if(\App\Models\LanguageGuidelines::doUserTeamSettingsDiffer($model, 'language-settings'))
<div class="pt-10">
    <x-jet-section-title>
        <x-slot name="title" class="ibarra-sub-title-h2">
            {{ __('guidelines.reset_to_team_suggestions_title') }}
        </x-slot>

        <x-slot name="description" class="lato-paragraph-text-p">
            <div class="container border-radius">
                {!! __('guidelines.reset_to_team_suggestions_description') !!}

                <form action="{{ route('user.language-settings-reset') }}">
                <x-jet-button>
                    {{ __('guidelines.reset_to_team') }}
                </x-jet-button>
                </form>
            </div>
        </x-slot>
    </x-jet-section-title>
</div>
@endif
