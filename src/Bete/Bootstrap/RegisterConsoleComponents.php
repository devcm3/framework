<?php

namespace Bete\Bootstrap;

use Bete\Foundation\Application;
use Bete\Support\ComponentRepository;
use Bete\Console\Request;
use Bete\Console\Route;

class RegisterConsoleComponents
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function bootstrap()
    {
        $this->app->singleton('request', function($app) {
            return new Request($app);
        });

        $this->app->singleton('route', function($app) {
            return new Route($app, $app['request']);
        });

        (new ComponentRepository($this->app))->load();
    }
}
