<?php

namespace App\Models;

interface GuidelinesInterface
{
    const GERMAN_GENDER_ENDING = ['*in', '_in', ':in', '/in', '/-in', 'In'];
    const GENDERED_ROLES_FORMAT = ['inclusive_gender' => 'guidelines.inclusive_gender', 'both' => 'guidelines.both', 'binary_gender' => 'guidelines.binary_gender',  'none' => 'guidelines.none'];
    const PREFERRED_VARIANTS_EN = ['' => 'guidelines.preferred_variants_none', 'en-US' => 'guidelines.preferred_variants_en_US', 'en-GB' => 'guidelines.preferred_variants_en_GB'];
    const PREFERRED_VARIANTS_DE = ['' => 'guidelines.preferred_variants_none', 'de-DE' => 'guidelines.preferred_variants_de_DE', 'de-CH' => 'guidelines.preferred_variants_de_CH', 'de-AT' => 'guidelines.preferred_variants_de_AT'];
    const PREFERRED_VARIANTS_FR = ['' => 'guidelines.preferred_variants_none', 'fr-FR' => 'guidelines.preferred_variants_fr_FR'];
    const DISABLED_CATEGORIES = [];
    const LANGUAGES = ['en', 'de', 'fr'];
}
