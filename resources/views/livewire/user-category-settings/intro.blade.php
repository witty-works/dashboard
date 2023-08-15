@if($model->subscribed())
@if(\App\Models\LanguageGuidelines::doUserTeamSettingsDiffer($model, 'category-settings'))
<div class="pt-10">
    <x-jet-section-title>
        <x-slot name="title" class="ibarra-sub-title-h2">
            {{ __('guidelines.reset_to_team_suggestions_title') }}
        </x-slot>

        <x-slot name="description" class="lato-paragraph-text-p">
            <div class="container border-radius" aria-labelledby="resetDescription">
                <span id="resetDescription">{!! __('guidelines.reset_to_team_suggestions_description') !!}</span>

                <form action="{{ route('user.category-settings-reset') }}" aria-label="{{ __('guidelines.form_reset_category_aria_label') }}">
                    <x-jet-button>
                        {{ __('guidelines.reset_to_team') }}
                    </x-jet-button>
                </form>
            </div>
        </x-slot>
    </x-jet-section-title>
</div>
@endif
@livewire('organization-category-settings.intro', ['model' => $model->currentTeam], key($model->currentTeam->id))
@endif
