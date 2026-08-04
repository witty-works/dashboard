# User & Team Management

Covers superadmin rights, the admin area and impersonation.

## Roles and permissions

Roles and permissions come from
[spatie/laravel-permission](https://github.com/spatie/laravel-permission). The
migration `2022_04_20_065350_roles` creates the baseline:

-   a `Superadmin` role
-   a `manage users` permission, granted to `Superadmin`

`manage users` is the only permission the application checks. It is configured in
`config/lumki.php` as `lumkiPermission` and guards every `/admin` route.

To make an account a superadmin, assign the role to it:

```bash
lando artisan tinker
>>> App\Models\User::where('email', 'you@example.com')->first()->assignRole('Superadmin');
```

## Admin area

The admin screens come from `kineticamobile/lumki` and are mounted under the
prefix set in `config/lumki.php` (`/admin`):

-   `/admin/users` — list users, edit their roles, jump into impersonation
-   `/admin/roles` — manage roles
-   `/admin/permissions` — manage permissions

They are behind `auth:sanctum`, `web` and `can:manage users`, so a user without
the permission gets a 403.

The views are published into `resources/views/vendor/lumki` and are edited in
this repository rather than in the package.

`SHOW_LUMKI` controls whether the admin entry appears in the navigation; the
routes exist either way and remain permission-guarded.

**Do not re-run `lumki:setup` on an existing checkout.** It is a one-time
scaffolding command that rewrites `app/Models/User.php` to add the `HasRoles`
trait. That trait is already applied, together with the conflict resolution the
model needs since spatie/laravel-permission 8 (see the `use HasRoles, HasTeams`
block), and the command would corrupt it.

## Impersonation

Impersonation uses
[lab404/laravel-impersonate](https://github.com/404labfr/laravel-impersonate):

-   `/impersonate/take/{id}` — start impersonating the user with that id
-   `/impersonate/leave` — return to your own account

Links to `impersonate/take/{id}` are generated in the admin user list, in the
team statistics email and in the PostHog sync payload, so a support request can
be opened directly as the affected user.

While impersonating, the admin navigation stays visible regardless of
`SHOW_LUMKI` so you can find your way back out.

## Teams

Teams come from Jetstream, with the personal team created during registration by
`App\Actions\Fortify\CreateNewUser`. `App\Models\Team` extends Cashier's billable
model, so subscription state and seat counts hang off the team rather than the
user — `Team::subscribed()`, `Team::planId()` and `Team::isPremium()` are the
usual entry points.
