# User & Team Management

Covers superadmin rights, two-factor sign-in, the admin area and
impersonation.

## Roles and permissions

Roles and permissions come from
[spatie/laravel-permission](https://github.com/spatie/laravel-permission). The
migration `2022_04_20_065350_roles` creates the baseline:

-   a `Superadmin` role
-   a `manage users` permission, granted to `Superadmin`

`manage users` is the only permission the application checks. Its name is set
in `config/admin.php` (`permission`); it guards every `/admin` route and decides
who may impersonate.

To create a superadmin, or make an existing verified account one, run on the
server:

```bash
lando artisan superadmin:create you@example.com "Your Name"
```

A new account is created verified, with an unusable password, and is mailed a
link to set one (the link uses `APP_URL`). An existing account whose address is
not verified is refused: anyone can register any address.

A superadmin must set up two-factor sign-in before using `/admin` or
impersonating anyone (see [Two-factor sign-in](#two-factor-sign-in)). A new
superadmin sets their password through the link, signs in, and on first
visiting `/admin` is sent to their profile page to set it up. Once they have
entered a code there, `/admin` opens in the same session.

## Two-factor sign-in

Every user can turn on two-factor sign-in on their profile page. It uses
Fortify's TOTP support, so any authenticator app works. Setting it up shows a QR
code and a setup key; it takes effect only once a code from the app has been
entered, and every change to it asks for the password first
(`config/fortify.php`). The profile page then shows recovery codes, each good
for one sign-in without the app.

When it is on, signing in with a password asks for a code or a recovery code,
at most five tries a minute. This also covers the browser extension and the
write page, which sign in through `/oauth/authorize` and the same sign-in page.

Signing in through Office SSO (the Word add-in) does not ask for a code:
Microsoft's sign-in handles the second factor there, under the policies of the
user's organisation. The profile page hides the setting in the add-in, except
for superadmins.

### Superadmins must use two-factor sign-in

For `/admin`, its Livewire actions and `/impersonate/take/{id}`
(`App\Support\SuperadminTwoFactor`):

-   a superadmin without confirmed two-factor sign-in is sent to the profile
    page, which says why, and cannot impersonate (`User::canImpersonate()`);
-   a superadmin with it must also have entered a code in the current session,
    at the sign-in challenge, when confirming the setup, or on
    `/user/two-factor-verify`, where they are sent otherwise. This applies to
    sessions started through Office SSO or a "remember me" cookie, which do not
    ask for a code. That check allows five tries a minute.

Turning two-factor sign-in off takes `/admin` away again until it is set up
anew.

Setting it up asks for the account's password. A superadmin whose account
was made through Office SSO has none yet: they set one through "Forgot your
password?" first.

The setup key and QR code are shown only while setting up, and the form's
display flags cannot be set by the browser, so neither the secret nor the
recovery codes can be read without the password.

### Recovering a locked-out account

Someone who lost both their authenticator app and their recovery codes cannot
sign in with a password. An operator removes two-factor sign-in from the
account on the server:

```bash
lando artisan two-factor:reset user@example.com
```

The command asks for confirmation and logs the reset. Check first that the
request comes from the account's owner, for example by a call to a number you
already know or a message from their organisation's administrator; an email
from the address alone is not enough, since a lost device can mean a lost
mailbox. The user then signs in with their password alone; a superadmin has to
set two-factor sign-in up again before using `/admin`.

Superadmins who still have access can check their recovery codes on the
profile page ("Show Recovery Codes") and generate new ones.

## Admin area

The admin pages are the dashboard's own, under the prefix set in
`config/admin.php` (`/admin`), behind `auth` and `can:manage users`, so a user
without the permission gets a 403. A superadmin also needs two-factor sign-in
(`superadmin.two-factor`):

-   `/admin/teams` (where `/admin` leads):
    -   every team with its owner, member count and rate limit, which only
        superadmins set; empty means no per-team limit. The limit is stored
        only; the NLP API does not enforce it yet;
    -   creating a team, which the superadmin owns until its owner takes over;
    -   "Configure", which switches to such a team so it can be set up on the
        normal team pages;
    -   inviting a team's owner;
    -   the teams waiting for their owner, whose invitations any superadmin
        can send again or revoke;
    -   deleting teams that a superadmin still owns.
-   `/admin/users` — search users by name or email, see every team each one
    belongs to and their role there, make or remove superadmins (the last one
    cannot remove their own role, and unverified accounts cannot become
    superadmins), impersonate, and delete accounts through `DeleteUser`, the
    same deletion as on the profile page.

Every superadmin sees an "Admin" entry in the navigation.

## Invite-only mode

With `INVITE_ONLY=true`:

-   only an address with a pending team invitation can sign up, on the sign-up
    page and through Office sign-up (the add-in is sent back with
    `?status=invite_only`);
-   only superadmins create teams, on `/admin/teams`; nobody gets a personal
    team;
-   a signed-in user without a team sees their pending invitations, or a note to
    ask for one; a superadmin without a team is sent to `/admin`.

A new team gets its owner like this:

1.  A superadmin creates the team and sets it up through "Configure".
2.  The superadmin invites its owner on `/admin/teams`. A team has at most one
    open owner invitation, and only while a superadmin owns it.
3.  When the invitation is accepted, the team becomes the invitee's and the
    superadmin leaves it. The owner then invites the team's members.

An account that owns a team with open invitations cannot be deleted until
they are revoked or accepted, just as a team with members has to be handed
over first.

Someone who signs up with an invited address but never verifies it blocks
that address for the real invitee, because addresses are unique. The invitee
gets the account back through "Forgot your password?", which proves the
mailbox is theirs.

## API keys

People create API keys on "API keys" (`/user/api-keys`), for tools such as
the Witty editor. The NLP API keeps the keys (its `/managed_keys` routes); the
dashboard stores none and shows a new key once. The page needs a verified
address and an NLP API endpoint with `APP_NLP_API_SYNC_CONFIGS` on; without
an endpoint the menu entry is hidden.

-   **A key belongs to a person and one of their teams.** It checks with that
    team's settings and counts against the team's LLM requests per minute,
    which superadmins set on `/admin/teams` and everyone sees on the page.
-   **A key can expire,** on a date chosen when it is created, and can be
    replaced: the new key keeps the name, team and expiry, and the old one
    works for a day, a week or not at all, as chosen.
-   **Team owners and admins** see their team's keys on the team page (whose,
    which name, when) and can revoke them.
-   **Keys are revoked** when their person leaves or is removed from the team,
    when the team is deleted or handed over, when the account is deleted and
    when the email address changes, since the API knows people by address.
    The NLP API also refuses a key whose team its person is no longer in, so
    a revocation that fails is caught by the next user sync.

With a second endpoint (`APP_NLP_API_SYNC_ENDPOINT_2`), keys are created and
listed on the first one and revoked on both, so both servers must share one
Redis for keys to work on either.

## Impersonation

Impersonation uses
[lab404/laravel-impersonate](https://github.com/404labfr/laravel-impersonate):

-   `/impersonate/take/{id}` — start impersonating the user with that id
-   `/impersonate/leave` — return to your own account

Only superadmins with two-factor sign-in may impersonate, and nobody may
impersonate a superadmin (`User::canImpersonate()` and `canBeImpersonated()`).
The session must have seen a code, as for `/admin`.

Links to `impersonate/take/{id}` are generated in the admin user list, in the
team statistics email and in the PostHog sync payload, so a support request can
be opened directly as the affected user.

While impersonating, the navigation shows a link back to your own account.

## Teams

Teams come from Jetstream, with the personal team created during registration by
`App\Actions\Fortify\CreateNewUser`.

Billing was removed, so there are no plans, no seat caps and no premium tier —
every team has the full feature set. `license_team_id` still decides which team's
guidelines apply to a user (managed under team settings), but the number of
licences is no longer limited.
