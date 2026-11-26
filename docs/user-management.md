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

-   `/admin/users` — search users by name or email, see every team each one
    belongs to and their role there, make or remove superadmins (the last one
    cannot remove their own role), impersonate, and delete accounts through
    `DeleteUser`, the same deletion as on the profile page.

Every superadmin sees an "Admin" entry in the navigation.

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
