<div>
@if($model->isPremium())
@livewire('organization-category-settings.intro', ['model' => $model->currentTeam], key($model->currentTeam->id))
@endif
</div>
