<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserGuidelines extends Model implements GuidelinesInterface
{
    use HasFactory;
    use GuidelinesUpdateTrait;

    protected $attributes = [
        'german_gender_ending' => '*in',
        'gendered_roles_format' => 'inclusive_gender',
        'singular_they' => false,
        'expert_mode' => false,
        'show_inspiration_alternatives' => false,
    ];

    protected $fillable = [
        'user_id',
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

    public function inPlaceUpateArray($element, $column, $enabled)
    {
        if ($enabled) {
            $query = "
                UPDATE language_guidelines
                    SET {$column} = JSON_REMOVE({$column}, JSON_UNQUOTE(JSON_SEARCH({$column}, 'one', :element)))
                WHERE id = :id AND JSON_CONTAINS({$column}, :element_json, '$')
            ";
        } else {
            $query = "
                UPDATE language_guidelines
                    SET {$column} = IF({$column} = '{}', :element_json, JSON_ARRAY_APPEND({$column}, '$', :element))
                WHERE id = :id AND NOT JSON_CONTAINS({$column}, :element_json, '$')
            ";
        }

        DB::statement($query, [':id' => $this->id, ':element' => $element, ':element_json' => "\"$element\""]);
    }
}
