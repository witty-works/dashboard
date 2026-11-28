<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * What the dashboard tells Sentry: the release it runs, taken from the image
 * build, and no personal data in breadcrumbs. Sessions and the queue are in
 * the database, so query values would carry addresses, password hashes and
 * job payloads, and log lines carry names and addresses.
 */
class SentryConfigTest extends TestCase
{
    private array $saved = [];

    protected function tearDown(): void
    {
        foreach ($this->saved as $name => $value) {
            $this->setEnv($name, $value);
        }

        parent::tearDown();
    }

    private function setEnv(string $name, ?string $value): void
    {
        if (!array_key_exists($name, $this->saved)) {
            $this->saved[$name] = getenv($name) === false ? null : getenv($name);
        }

        if ($value === null) {
            putenv($name);
            unset($_ENV[$name], $_SERVER[$name]);
        } else {
            putenv("{$name}={$value}");
            $_ENV[$name] = $_SERVER[$name] = $value;
        }
    }

    private function sentryConfig(): array
    {
        return require base_path('config/sentry.php');
    }

    public function test_the_release_is_the_version_the_image_was_built_from(): void
    {
        $this->setEnv('SENTRY_RELEASE', null);
        $this->setEnv('APP_VERSION', '2.0.0-rc1');
        $this->setEnv('GIT_REVISION', 'abc123');

        $this->assertSame('2.0.0-rc1', $this->sentryConfig()['release']);
    }

    public function test_without_a_version_the_release_is_the_git_revision(): void
    {
        $this->setEnv('SENTRY_RELEASE', null);
        $this->setEnv('APP_VERSION', '');
        $this->setEnv('GIT_REVISION', 'abc123');

        $this->assertSame('abc123', $this->sentryConfig()['release']);
    }

    public function test_sentry_release_overrides_both_and_none_gives_no_release(): void
    {
        $this->setEnv('SENTRY_RELEASE', 'custom');
        $this->setEnv('APP_VERSION', '2.0.0');
        $this->assertSame('custom', $this->sentryConfig()['release']);

        $this->setEnv('SENTRY_RELEASE', null);
        $this->setEnv('APP_VERSION', null);
        $this->setEnv('GIT_REVISION', '');
        $this->assertNull($this->sentryConfig()['release']);
    }

    public function test_breadcrumbs_carry_no_query_values_or_log_lines(): void
    {
        $breadcrumbs = $this->sentryConfig()['breadcrumbs'];

        $this->assertFalse($breadcrumbs['sql_bindings']);
        $this->assertFalse($breadcrumbs['logs']);
        $this->assertFalse($this->sentryConfig()['send_default_pii']);
    }
}
