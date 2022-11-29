<div>
@if(\App\Models\LanguageGuidelines::doUserGuidelinesTeamDiffer(Auth::user()))
    <div class="py-10">
    <x-jet-section-title>
        <x-slot name="title" class="ibarra-sub-title-h2">
            {{ __('guidelines.reset_to_team_suggestions_title') }}
        </x-slot>

        <x-slot name="description" class="lato-paragraph-text-p">
            <div>
                {!! __('guidelines.reset_to_team_suggestions_description') !!}
            </div>

            <div>
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
</div>
