<?php

namespace App\Console\Localization;

use Illuminate\Routing\Router;
use Mcamara\LaravelLocalization\Commands\RouteTranslationsListCommand as BaseCommand;

/**
 * Counterpart to RouteTranslationsCacheCommand: without this the package's list
 * command inherits #[AsCommand(name: 'route:list')] from Laravel's
 * RouteListCommand and takes over `route:list`, which then fails because its own
 * required `locale` argument is never registered on that command.
 */
class RouteTranslationsListCommand extends BaseCommand
{
    protected $name = 'route:trans:list';

    public function __construct(Router $router)
    {
        parent::__construct($router);

        $this->setName('route:trans:list');
    }
}
