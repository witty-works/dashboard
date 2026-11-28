<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\FalsePositive;
use App\Models\TermReplacement;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * The guideline lists (ignored words, dictionary, privacy domains) are edited
 * through Livewire actions that take an entry's id from the browser. The id
 * is the client's to choose, so each action has to check that the entry
 * belongs to the user or team the component shows, not only that the user
 * may edit that user's or team's guidelines.
 */
class GuidelineOwnershipTest extends TestCase
{
    use RefreshDatabase;

    private function signedInUser(): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        return $user->refresh();
    }

    /** What each entry type is called in the forms, and its text. */
    private const TEXT = [
        FalsePositive::class => ['false_positive', 'not-yours'],
        TermReplacement::class => ['term', 'not-yours'],
        Domain::class => ['domain', 'not-yours.example'],
    ];

    /** An entry of `$class` belonging to `$owner` (`user_id` or `team_id`). */
    private function entry(string $class, string $ownerColumn, int $ownerId): Model
    {
        [$field, $text] = self::TEXT[$class];
        $extra = $class === TermReplacement::class
            ? ['replacement' => 'still-not-yours', 'language_code' => 'en']
            : [];

        return $class::forceCreate([$field => $text, ...$extra, $ownerColumn => $ownerId]);
    }

    public static function userActions(): array
    {
        return [
            'ignored word: delete' => [\App\Livewire\UserFalsePositive\Show::class, 'deleteFalsePositive', FalsePositive::class],
            'ignored word: edit' => [\App\Livewire\UserFalsePositive\Show::class, 'editFalsePositive', FalsePositive::class],
            'ignored word: form' => [\App\Livewire\UserFalsePositive\Form::class, 'edit', FalsePositive::class],
            'dictionary: delete' => [\App\Livewire\UserTermReplacement\Show::class, 'deleteTermReplacement', TermReplacement::class],
            'dictionary: edit' => [\App\Livewire\UserTermReplacement\Show::class, 'editTermReplacement', TermReplacement::class],
            'dictionary: form' => [\App\Livewire\UserTermReplacement\Form::class, 'edit', TermReplacement::class],
            'domain: delete' => [\App\Livewire\UserDomain\Show::class, 'deleteDomain', Domain::class],
            'domain: edit' => [\App\Livewire\UserDomain\Show::class, 'editDomain', Domain::class],
            'domain: form' => [\App\Livewire\UserDomain\Form::class, 'edit', Domain::class],
        ];
    }

    public static function teamActions(): array
    {
        return [
            'ignored word: delete' => [\App\Livewire\OrganizationFalsePositive\Show::class, 'deleteFalsePositive', FalsePositive::class],
            'ignored word: edit' => [\App\Livewire\OrganizationFalsePositive\Show::class, 'editFalsePositive', FalsePositive::class],
            'ignored word: form' => [\App\Livewire\OrganizationFalsePositive\Form::class, 'edit', FalsePositive::class],
            'dictionary: delete' => [\App\Livewire\OrganizationTermReplacement\Show::class, 'deleteTermReplacement', TermReplacement::class],
            'dictionary: edit' => [\App\Livewire\OrganizationTermReplacement\Show::class, 'editTermReplacement', TermReplacement::class],
            'dictionary: form' => [\App\Livewire\OrganizationTermReplacement\Form::class, 'edit', TermReplacement::class],
            'domain: delete' => [\App\Livewire\OrganizationDomain\Show::class, 'deleteDomain', Domain::class],
            'domain: edit' => [\App\Livewire\OrganizationDomain\Show::class, 'editDomain', Domain::class],
            'domain: form' => [\App\Livewire\OrganizationDomain\Form::class, 'edit', Domain::class],
        ];
    }

    /** What a permitted action leaves behind: deleted, sent to the form, or in the form. */
    private function assertActed($test, string $component, string $action, Model $entry): void
    {
        if (str_starts_with($action, 'delete')) {
            $this->assertModelMissing($entry);
        } elseif ($action === 'edit') {
            [$field, $text] = self::TEXT[$entry::class];
            $test->assertSet($field, $text);
            $this->assertModelExists($entry);
        } else {
            $test->assertDispatched('edit');
            $this->assertModelExists($entry);
        }
    }

    #[DataProvider('userActions')]
    public function test_a_user_cannot_touch_another_users_entry(string $component, string $action, string $class): void
    {
        $user = $this->signedInUser();
        $other = $this->signedInUser();
        $entry = $this->entry($class, 'user_id', $other->id);
        $this->actingAs($user);

        Livewire::test($component, ['model' => $user])
            ->call($action, $entry->id)
            ->assertForbidden();

        $this->assertModelExists($entry);
    }

    #[DataProvider('userActions')]
    public function test_a_user_cannot_touch_a_teams_entry_through_their_own_list(string $component, string $action, string $class): void
    {
        $user = $this->signedInUser();
        $entry = $this->entry($class, 'team_id', $user->currentTeam->id);
        $this->actingAs($user);

        Livewire::test($component, ['model' => $user])
            ->call($action, $entry->id)
            ->assertForbidden();

        $this->assertModelExists($entry);
    }

    #[DataProvider('teamActions')]
    public function test_a_team_editor_cannot_touch_another_teams_entry(string $component, string $action, string $class): void
    {
        $user = $this->signedInUser();
        $other = $this->signedInUser();
        $entry = $this->entry($class, 'team_id', $other->currentTeam->id);
        $this->actingAs($user);

        Livewire::test($component, ['model' => $user->currentTeam])
            ->call($action, $entry->id)
            ->assertForbidden();

        $this->assertModelExists($entry);
    }

    #[DataProvider('teamActions')]
    public function test_a_member_without_edit_guidelines_cannot_touch_the_teams_entry(string $component, string $action, string $class): void
    {
        $owner = $this->signedInUser();
        $team = $owner->currentTeam;
        $member = User::factory()->withPersonalTeam()->create();
        $team->users()->attach($member, ['role' => 'user']);
        $member->switchTeam($team);
        $entry = $this->entry($class, 'team_id', $team->id);
        $this->actingAs($member->refresh());

        Livewire::test($component, ['model' => $team])
            ->call($action, $entry->id)
            ->assertForbidden();

        $this->assertModelExists($entry);
    }

    #[DataProvider('userActions')]
    public function test_a_user_can_still_delete_and_edit_their_own_entry(string $component, string $action, string $class): void
    {
        $user = $this->signedInUser();
        $entry = $this->entry($class, 'user_id', $user->id);
        $this->actingAs($user);

        $test = Livewire::test($component, ['model' => $user])
            ->call($action, $entry->id)
            ->assertOk();

        $this->assertActed($test, $component, $action, $entry);
    }

    #[DataProvider('teamActions')]
    public function test_a_team_editor_can_still_delete_and_edit_their_teams_entry(string $component, string $action, string $class): void
    {
        $user = $this->signedInUser();
        $entry = $this->entry($class, 'team_id', $user->currentTeam->id);
        $this->actingAs($user);

        $test = Livewire::test($component, ['model' => $user->currentTeam])
            ->call($action, $entry->id)
            ->assertOk();

        $this->assertActed($test, $component, $action, $entry);
    }

    #[DataProvider('userActions')]
    public function test_the_client_cannot_point_a_component_at_another_user(string $component, string $action, string $class): void
    {
        $user = $this->signedInUser();
        $other = $this->signedInUser();
        $this->actingAs($user);

        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test($component, ['model' => $user])->set('model', $other->id);
    }

    public function test_a_user_cannot_add_an_ignored_word_to_another_users_list(): void
    {
        $user = $this->signedInUser();
        $other = $this->signedInUser();
        $this->actingAs($user);

        Livewire::test(\App\Livewire\UserFalsePositive\Form::class, ['model' => $other])
            ->set('false_positive', 'planted')
            ->call('storeFalsePositive')
            ->assertForbidden();

        $this->assertDatabaseMissing('false_positives', ['false_positive' => 'planted']);
    }

    /**
     * Every component bound to a user or team keeps it from the server: a
     * new one that forgets #[Locked] fails here.
     */
    public function test_every_components_model_is_locked(): void
    {
        $unlocked = [];
        foreach ((new Finder())->files()->in(app_path('Livewire'))->name('*.php') as $file) {
            $class = 'App\\Livewire\\' . str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());
            if (! class_exists($class) || ! is_subclass_of($class, Component::class)) {
                continue;
            }
            $reflection = new ReflectionClass($class);
            if ($reflection->isAbstract() || ! $reflection->hasProperty('model')) {
                continue;
            }
            if (! $reflection->getProperty('model')->getAttributes(Locked::class)) {
                $unlocked[] = $class;
            }
        }

        $this->assertSame([], $unlocked, 'Components whose $model is not #[Locked]');
    }
}
