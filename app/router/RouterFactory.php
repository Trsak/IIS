<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
    use Nette\StaticClass;

    public static function createRouter(): RouteList
    {
        $router = new RouteList;

        $router[] = new Route('literature/<id \d+>[/<title>]', [
            'presenter' => 'Literature',
            'action' => 'default',
            'title' => [
                Route::FILTER_OUT => 'Nette\Utils\Strings::webalize',
            ],
        ]);

        $router[] = new Route('literatures[/<page \d+>]', [
            'presenter' => 'Homepage',
            'action' => 'default',
            'page' => 1
        ]);

        $router[] = new Route('<presenter>/<action>', 'Homepage:default');
        return $router;
    }
}
