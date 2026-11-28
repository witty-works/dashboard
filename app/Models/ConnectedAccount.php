<?php

namespace App\Models;

use App\Events\ConnectedAccountCreated;
use App\Events\ConnectedAccountDeleted;
use App\Events\ConnectedAccountUpdated;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Previously extended JoelButcher\Socialstream\ConnectedAccount. That package was
 * archived upstream in December 2025 and never supported Laravel 13, so the base
 * model was absorbed here. The package's Inertia and OAuth2-credentials helpers
 * were dropped because this application never used them.
 */
class ConnectedAccount extends Model
{
    use HasFactory;
    use HasTimestamps;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'provider',
        'provider_id',
        'name',
        'nickname',
        'email',
        'avatar_path',
        'token',
        'refresh_token',
        'expires_at',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => ConnectedAccountCreated::class,
        'updated' => ConnectedAccountUpdated::class,
        'deleted' => ConnectedAccountDeleted::class,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Get the user this connected account belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', (new User)->getAuthIdentifierName());
    }
}
