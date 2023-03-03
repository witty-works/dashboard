<?php

use App\Models\LanguageGuidelines;
use App\Models\Team;
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
        $query = Team::query();
        foreach ($query->cursor() as $team) {
            if ($team->subscribed()) {
                continue;
            }

            if ($team->owner->languageGuidelines) {
                $userGuidelines = $team->owner->languageGuidelines;
                $languageGuidelines = LanguageGuidelines::firstOrNew(['team_id' => $team->id]);
                $languageGuidelines->preferred_languages = $userGuidelines->preferred_languages;
                $languageGuidelines->preferred_variants = $userGuidelines->preferred_variants;
                $languageGuidelines->german_gender_ending = $userGuidelines->german_gender_ending;
                $languageGuidelines->gendered_roles_format = $userGuidelines->gendered_roles_format;
                $languageGuidelines->singular_they = $userGuidelines->singular_they;
                $languageGuidelines->expert_mode = $userGuidelines->expert_mode;
                $languageGuidelines->show_inspiration_alternatives = $userGuidelines->show_inspiration_alternatives;
                $languageGuidelines->simple_language = $userGuidelines->simple_language;
                $languageGuidelines->customized = $userGuidelines->customized;
                $languageGuidelines->english_rules_force = true;
                $languageGuidelines->expert_mode_force = true;
                $languageGuidelines->preferred_variants_force = true;
                $languageGuidelines->german_rules_force = true;
                $languageGuidelines->show_inspiration_alternatives_force = true;
                $languageGuidelines->saveQuietly();
            }
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
