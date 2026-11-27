<?php

namespace Tests\Feature;

use App\Jobs\SendEventToPosthog;
use App\Jobs\SyncToPosthog;
use App\Models\Domain;
use App\Models\FalsePositive;
use App\Models\TermReplacement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

/**
 * What the dashboard itself sends to PostHog: counts and settings, not the
 * contents of people's lists (an ignored word is often a person's name, a
 * domain names the page someone was on) and not identifying fields nothing
 * reads. The analytics pages query the clients' check events only.
 */
class PosthogContentsTest extends TestCase
{
    use RefreshDatabase;

    private function sentProperties(): array
    {
        $properties = [];
        Bus::assertDispatched(SendEventToPosthog::class, function (SendEventToPosthog $job) use (&$properties) {
            $properties[] = (fn () => $this->properties)->call($job);

            return true;
        });

        return array_merge(...$properties);
    }

    public function test_list_events_carry_the_count_not_the_entry(): void
    {
        Bus::fake();
        $user = User::factory()->withPersonalTeam()->create();

        FalsePositive::forceCreate(['user_id' => $user->id, 'false_positive' => 'Jane Doe'])->dispatchEventToPosthog();
        TermReplacement::forceCreate(['user_id' => $user->id, 'term' => 'guys', 'replacement' => 'everyone', 'language_code' => 'en'])->dispatchEventToPosthog();
        Domain::forceCreate(['user_id' => $user->id, 'domain' => 'intranet.example'])->dispatchEventToPosthog();

        $properties = $this->sentProperties();
        $this->assertArrayHasKey('total', $properties);
        foreach (['false_positive', 'replacement', 'domain'] as $content) {
            $this->assertArrayNotHasKey($content, $properties);
        }
        $this->assertStringNotContainsString('Jane Doe', json_encode($properties));
        $this->assertStringNotContainsString('intranet.example', json_encode($properties));
    }

    public function test_person_properties_carry_no_identifying_fields_nothing_reads(): void
    {
        $user = User::factory()->withPersonalTeam()->create(['company_name' => 'ACME']);

        $properties = $user->getUserData();

        foreach (['impersonate_url', 'company', 'how_did_you_find'] as $field) {
            $this->assertArrayNotHasKey($field, $properties);
        }
        $this->assertSame($user->posthogId(), $properties['dashboard_id']);
    }

    public function test_team_properties_carry_no_impersonation_link(): void
    {
        $team = User::factory()->withPersonalTeam()->create()->ownedTeams()->first();

        $properties = SyncToPosthog::teamProperties($team, false);

        $this->assertArrayNotHasKey('impersonate_url', $properties);
        $this->assertSame($team->name, $properties['name']);
    }
}
