<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Support\SuperadminTwoFactor;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Contracts\DeletesUsers;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * The superadmins' list of users: find them, see every team they are in,
 * make or remove superadmins, impersonate, and delete accounts the same way
 * self-service deletion does.
 */
class Users extends Component
{
    use WithPagination;

    // In the address too (?search=), so a link can open the page on a user.
    #[Url]
    public string $search = '';

    /**
     * Every request, not only the first: an update request renders the page
     * again from a snapshot, which is signed but not tied to the session.
     */
    public function boot(): void
    {
        $this->authorizeSuperadmin();
    }

    public function mount(): void
    {
        $this->authorizeSuperadmin();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function makeSuperadmin(int $userId): void
    {
        $this->authorizeSuperadmin();

        $user = User::findOrFail($userId);
        // Anyone can register any address before its owner does.
        if (! $user->hasVerifiedEmail()) {
            $this->addError('superadmin', __('content.admin_unverified_cannot_be_superadmin', ['email' => $user->email]));

            return;
        }

        $user->assignRole(config('admin.role'));
    }

    public function removeSuperadmin(int $userId): void
    {
        $this->authorizeSuperadmin();

        $user = User::findOrFail($userId);
        if ($user->hasRole(config('admin.role')) && User::role(config('admin.role'))->count() <= 1) {
            $this->addError('superadmin', __('content.admin_last_superadmin'));

            return;
        }

        $user->removeRole(config('admin.role'));
    }

    public function deleteUser(int $userId, DeletesUsers $deleter): void
    {
        $this->authorizeSuperadmin();

        $user = User::findOrFail($userId);
        // Their own account is deleted from the profile page, which asks for
        // their password.
        abort_if($user->is(auth()->user()), 403);

        try {
            $deleter->delete($user);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $key => $messages) {
                foreach ($messages as $message) {
                    $this->addError($key, $message);
                }
            }
        }
    }

    public function render()
    {
        $term = trim($this->search);
        $users = User::query()
            ->with(['ownedTeams', 'teams', 'roles'])
            ->when($term !== '', function ($query) use ($term) {
                $like = '%' . addcslashes($term, '%_\\') . '%';
                $query->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('email', 'like', $like));
            })
            ->orderBy('name')
            ->paginate(25);

        return view('livewire.admin.users', [
            'users' => $users,
            'superadminRole' => config('admin.role'),
        ]);
    }

    private function authorizeSuperadmin(): void
    {
        // Livewire's update requests do not run the page's two-factor
        // middleware, so the requirement is checked here as well.
        abort_unless(
            auth()->user()?->can(config('admin.permission')) && SuperadminTwoFactor::passes(auth()->user()),
            403
        );
    }
}
