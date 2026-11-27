<?php

namespace App\Livewire\Profile;

use Laravel\Jetstream\Http\Livewire\TwoFactorAuthenticationForm as JetstreamForm;
use Livewire\Attributes\Locked;

/**
 * Jetstream's form, with the flags that show the secret and the recovery
 * codes locked: a client could otherwise set them in an update request and
 * read both without the password, then use the secret to pass the
 * superadmin code check.
 */
class TwoFactorAuthenticationForm extends JetstreamForm
{
    #[Locked]
    public $showingQrCode = false;

    #[Locked]
    public $showingConfirmation = false;

    #[Locked]
    public $showingRecoveryCodes = false;
}
