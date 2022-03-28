<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermReplacement extends Model
{
    use HasFactory;
    use OrganizationGuidelinesUpdateTrait;

    const LANGUAGE_CODE = ['' => 'content.any', 'de' => 'content.de', 'en' => 'content.en'];
}
