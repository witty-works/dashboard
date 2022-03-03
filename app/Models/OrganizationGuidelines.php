<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationGuidelines extends Model
{
    use HasFactory;

    const GERMAN_GENDER_ENDING = [':in', '*in', '/in', '_in', 'In', '/-in'];
    const GENDERED_ROLES_FORMAT = ['gender_inclusive' => 'guidelines.gender_inclusive', 'both' => 'guidelines.both', 'gender_binary' => 'guidelines.gender_binary'];
    const PREFERRED_VARIANTS_EN = ['' => 'guidelines.preferred_variants_none', 'en_US' => 'guidelines.preferred_variants_en_US', 'en_GB' => 'guidelines.preferred_variants_en_GB'];
    const PREFERRED_VARIANTS_DE = ['' => 'guidelines.preferred_variants_none', 'de_DE' => 'guidelines.preferred_variants_de_DE', 'de_CH' => 'guidelines.preferred_variants_de_CH', 'de_AT' => 'guidelines.preferred_variants_de_AT'];
    const DISABLED_CATEGORIES = ['orthography', 'casing', 'style', 'inclusive'];

    protected $attributes = [
        'german_gender_ending' => ':in',
        'gendered_roles_format' => 'gender_inclusive',
        'store_context' => true,
        'preferred_variants' => '{"de-DE", "en-US"}',
        'singular_they' => false,
        'expert_mode' => false,
        'disabled_categories' => null,
    ];

    protected $fillable = [
        'team_id',
    ];
}
