<?php

namespace App\Console\Localization;

use Illuminate\Filesystem\Filesystem;
use Mcamara\LaravelLocalization\Commands\RouteTranslationsCacheCommand as BaseCommand;

/**
 * mcamara/laravel-localization names its route commands with `protected $name`,
 * but they extend Laravel's route commands, which since Laravel 13 carry an
 * #[AsCommand] attribute. Symfony 8 walks the parent classes for that attribute
 * and it takes precedence over `$name`, so the package's commands register as
 * `route:cache` and `route:list`: `route:list` then fails on its own missing
 * `locale` argument, and `route:trans:cache` — which the Platform.sh deploy hook
 * runs — stops existing altogether.
 *
 * Declaring the attribute on a subclass does not help, because the parent's is
 * still preferred, so the name is set explicitly after construction. The package
 * resolves its commands from the container by alias, and AppServiceProvider
 * repoints those aliases here.
 */
class RouteTranslationsCacheCommand extends BaseCommand
{
    protected $name = 'route:trans:cache';

    public function __construct(Filesystem $files)
    {
        parent::__construct($files);

        $this->setName('route:trans:cache');
    }
}
