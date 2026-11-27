<?php

namespace App\Http\Controllers;

use App\Support\SuperadminTwoFactor;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Events\TwoFactorAuthenticationFailed;
use Laravel\Fortify\Events\ValidTwoFactorAuthenticationCodeProvided;
use Laravel\Fortify\Fortify;

/**
 * Asks a signed-in superadmin for a code before /admin or impersonation when
 * the session has not seen one: after Office SSO or a "remember me" cookie.
 * Takes a code from the app or a recovery code, like the sign-in challenge.
 */
class TwoFactorVerificationController extends Controller
{
    public function show(Request $request)
    {
        if (! $request->user()->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('profile.show');
        }

        return view('auth.two-factor-challenge', [
            'action' => route('two-factor.verify.store'),
            'intro' => __('content.two_factor_verify_intro'),
            'submit' => __('content.continue'),
        ]);
    }

    public function store(Request $request, TwoFactorAuthenticationProvider $provider)
    {
        $user = $request->user();

        if (! $user->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('profile.show');
        }

        $request->validate([
            'code' => 'nullable|string',
            'recovery_code' => 'nullable|string',
        ]);

        $code = (string) $request->input('code');
        $recoveryCode = (string) $request->input('recovery_code');

        if ($code !== '') {
            $valid = $provider->verify(Fortify::currentEncrypter()->decrypt($user->two_factor_secret), $code);
        } else {
            $valid = $recoveryCode !== '' && collect($user->recoveryCodes())
                ->contains(fn ($stored) => hash_equals($stored, $recoveryCode));
            if ($valid) {
                $user->replaceRecoveryCode($recoveryCode);
            }
        }

        if (! $valid) {
            event(new TwoFactorAuthenticationFailed($user));
            $field = $code !== '' || $recoveryCode === '' ? 'code' : 'recovery_code';

            throw ValidationException::withMessages([
                $field => [__('content.two_factor_invalid_code')],
            ]);
        }

        event(new ValidTwoFactorAuthenticationCodeProvided($user));
        // A new session id with the new rights: one known before the code
        // (fixation) does not gain /admin.
        $request->session()->regenerate();
        SuperadminTwoFactor::markVerified($user);

        return redirect()->intended(route('admin.home'));
    }
}
