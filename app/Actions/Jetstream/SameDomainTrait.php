<?php

namespace App\Actions\Jetstream;

trait SameDomainTrait
{
    protected function getEmailDomain($email)
    {
        return substr(strrchr($email, '@'), 1);
    }

    /**
     * Ensure that the user email is the same domain as the team owner
     *
     * @param  mixed  $team
     * @param  string  $email
     * @return \Closure
     */
    protected function ensureOwnerEmailHasSameDomain($team, string $email)
    {
        return function ($validator) use ($team, $email) {
            $validator->errors()->addIf(
                $this->getEmailDomain($team->owner->email) !== $this->getEmailDomain($email),
                'email',
                __('This user email domain must be ":domain", the same as owner.', ['domain' => $this->getEmailDomain($team->owner->email)])
            );
        };
    }
}
