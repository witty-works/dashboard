<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationGuidelines extends Model
{
    use HasFactory;

    const GERMAN_GENDER_ENDING = [':in', '*in', '/in', '_in', 'In', '/-in'];
    const GENDERED_ROLES_FORMAT = ['gender_inclusive' => 'guidelines.gender_inclusive', 'both' => 'guidelines.both', 'gender_binary' => 'guidelines.gender_binary'];

    protected $attributes = [
        'german_gender_ending' => ':in',
        'gendered_roles_format' => 'gender_inclusive',
        'store_context' => true,
    ];

    protected $fillable = [
        'team_id',
    ];
}
