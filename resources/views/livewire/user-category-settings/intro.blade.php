<div>
@if($model->subscribed())
@livewire('organization-category-settings.intro', ['model' => $model->currentTeam], key($model->currentTeam->id))
@endif
</div>
