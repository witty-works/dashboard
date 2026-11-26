# User & Team Management

Covers superadmin rights, the admin area and impersonation.

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

## Admin area

The admin pages are the dashboard's own, under the prefix set in
`config/admin.php` (`/admin`), behind `auth` and `can:manage users`, so a user
without the permission gets a 403:

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

## Impersonation

Impersonation uses
[lab404/laravel-impersonate](https://github.com/404labfr/laravel-impersonate):

-   `/impersonate/take/{id}` — start impersonating the user with that id
-   `/impersonate/leave` — return to your own account

Only superadmins may impersonate, and nobody may impersonate a superadmin
(`User::canImpersonate()` and `canBeImpersonated()`).

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
