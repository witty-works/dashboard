<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $query = User::query();
        foreach ($query->cursor() as $user) {
            $this->updateGuidelines($user);
        }

        $query = Team::query();
        foreach ($query->cursor() as $team) {
            $this->updateGuidelines($team);
        }
    }

    protected function updateGuidelines($model)
    {
        $guidelines = $model->languageGuidelines;
        if (!$guidelines) {
            return;
        }

        if (
            is_array($guidelines->disabled_categories_force)
            && in_array('orthography', $guidelines->disabled_categories_force)
        ) {
            $guidelines->inPlaceUpateArray('orthography', 'disabled_categories_force', true);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
