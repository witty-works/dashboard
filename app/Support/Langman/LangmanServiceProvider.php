<?php

namespace App\Support\Langman;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

/**
 * Vendored from themsaid/laravel-langman (MIT, see LICENSE.md in this
 * directory). Upstream has been unmaintained since 2017 and pins
 * illuminate/* to ~5.1, so the project ran a fork at
 * witty-works/laravel-langman purely to widen that constraint. Keeping the
 * source in-repo removes the fork and the Laravel version ceiling with it.
 *
 * The config lives at config/langman.php, so the package's publish/merge
 * handling is no longer needed.
 */
class LangmanServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Manager::class, function () {
            return new Manager(
                new Filesystem,
                $this->app['config']['langman.path'],
                array_merge(
                    $this->app['config']['langman.code_paths'],
                    $this->app['config']['view.paths'],
                    [$this->app['path']]
                ),
                $this->app['config']['langman.functions'],
                $this->app['config']['langman.target_language']
            );
        });

        $this->commands([
            Commands\MissingCommand::class,
            Commands\RemoveCommand::class,
            Commands\TransCommand::class,
            Commands\ShowCommand::class,
            Commands\FindCommand::class,
            Commands\SyncCommand::class,
            Commands\RenameCommand::class,
        ]);
    }
}
