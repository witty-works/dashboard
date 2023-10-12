<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $query = Team::query();
        foreach ($query->cursor() as $team) {
            $this->updateGuidelines($team);
        }

        $query = User::query();
        foreach ($query->cursor() as $user) {
            $this->updateGuidelines($user);
        }
    }

    protected function updateGuidelines($model)
    {
        if ($model->subscribed()) {
            return;
        }

        $guidelines = $model->languageGuidelines;
        if (!$guidelines) {
            return;
        }

        $guidelines->show_inspiration_alternatives = false;
        $guidelines->save();
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
