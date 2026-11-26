<?php

namespace App\Livewire\Admin;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Jetstream\Contracts\DeletesTeams;
use Livewire\Component;

/**
 * The superadmins' list of teams: every team with its owner, members and
 * rate limit, which only superadmins set. Superadmins create teams here,
 * configure them while they still own them, and delete them while they are
 * still a superadmin's.
 */
class Teams extends Component
{
    public const MAX_LIMIT = 4294967295;

    public string $newName = '';

    public $newLimit = null;

    /** @var array<int, int|string|null> the limit field of each team, by team id */
    public array $limits = [];

    public function mount(): void
    {
        $this->authorizeSuperadmin();

        $this->limits = Team::pluck('llm_requests_per_minute', 'id')->all();
    }

    public function createTeam(): void
    {
        $this->authorizeSuperadmin();

        $this->validate([
            'newName' => ['required', 'string', 'max:255'],
            'newLimit' => $this->limitRules(),
        ]);

        $user = auth()->user();
        // Directly, not through TeamPolicy: its "no team yet" rule is for
        // users creating their own team, not for superadmins setting one up.
        $team = DB::transaction(fn () => Team::forceCreate([
            'user_id' => $user->id,
            'name' => trim($this->newName),
            'personal_team' => false,
            'llm_requests_per_minute' => $this->normalise($this->newLimit),
        ]));
        $user->switchTeam($team);

        $this->limits[$team->id] = $team->llm_requests_per_minute;
        $this->reset('newName', 'newLimit');
    }

    public function saveLimit(int $teamId): void
    {
        $this->authorizeSuperadmin();

        $team = Team::findOrFail($teamId);
        $this->validate(["limits.$teamId" => $this->limitRules()]);

        $team->forceFill(['llm_requests_per_minute' => $this->normalise($this->limits[$teamId] ?? null)])->save();
    }

    /** Switch to a team the superadmin owns, to configure it on the team pages. */
    public function configure(int $teamId)
    {
        $this->authorizeSuperadmin();

        $team = Team::findOrFail($teamId);
        $user = auth()->user();
        abort_unless($user->ownsTeam($team), 403);

        $user->switchTeam($team);

        return $this->redirectRoute('teams.show');
    }

    /** Only teams that are still a superadmin's: a handed-over team has its own owner. */
    public function deleteTeam(int $teamId, DeletesTeams $deleter): void
    {
        $this->authorizeSuperadmin();

        $team = Team::findOrFail($teamId);
        abort_unless($team->owner?->can(config('admin.permission')), 403);

        $deleter->delete($team);
        unset($this->limits[$teamId]);
    }

    public function render()
    {
        return view('livewire.admin.teams', [
            'teams' => Team::with('owner')->withCount('users')->orderBy('name')->get(),
            'superadminIds' => User::permission(config('admin.permission'))->pluck('id')->all(),
        ]);
    }

    private function limitRules(): array
    {
        return ['nullable', 'integer', 'min:1', 'max:' . self::MAX_LIMIT];
    }

    private function normalise($limit): ?int
    {
        return $limit === null || $limit === '' ? null : (int) $limit;
    }

    private function authorizeSuperadmin(): void
    {
        abort_unless(auth()->user()?->can(config('admin.permission')), 403);
    }
}
