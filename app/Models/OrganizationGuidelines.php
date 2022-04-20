<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationGuidelines extends Model
{
    use HasFactory;
    use OrganizationGuidelinesUpdateTrait;

    const GERMAN_GENDER_ENDING = [':in', '*in', '/in', '_in', 'In', '/-in'];
    const GENDERED_ROLES_FORMAT = ['inclusive_gender' => 'guidelines.inclusive_gender', 'both' => 'guidelines.both', 'binary_gender' => 'guidelines.binary_gender'];
    const PREFERRED_VARIANTS_EN = ['' => 'guidelines.preferred_variants_none', 'en-US' => 'guidelines.preferred_variants_en_US', 'en-GB' => 'guidelines.preferred_variants_en_GB'];
    const PREFERRED_VARIANTS_DE = ['' => 'guidelines.preferred_variants_none', 'de-DE' => 'guidelines.preferred_variants_de_DE', 'de-CH' => 'guidelines.preferred_variants_de_CH', 'de-AT' => 'guidelines.preferred_variants_de_AT'];
    const DISABLED_CATEGORIES = ['orthography', 'casing', 'style', 'inclusive'];
    const DISABLED_CATEGORIES_ORTHOGRAPHY = ['orthography', 'casing'];
    const DISABLED_CATEGORIES_INCLUSIVE = ['inclusive'];
    const DISABLED_CATEGORIES_STYLE = ['style'];
    const LANGUAGES = ['en', 'de'];

    protected $attributes = [
        'german_gender_ending' => ':in',
        'gendered_roles_format' => 'inclusive_gender',
        'singular_they' => false,
        'expert_mode' => false,
        'show_inspiration_alternatives' => false,
    ];

    protected $fillable = [
        'team_id',
        'preferred_variants',
        'disabled_categories',
        'disabled_categories_force',
    ];

    protected $casts = [
        'preferred_variants' => 'json',
        'disabled_categories' => 'json',
        'disabled_categories_force' => 'json',
    ];

    public function __construct(array $attributes = [])
    {
        $attributes += [
            'preferred_variants' => ['de-DE', 'en-US'],
            'disabled_categories' => [],
            'disabled_categories_force' => [],
        ];

        parent::__construct($attributes);
    }
}