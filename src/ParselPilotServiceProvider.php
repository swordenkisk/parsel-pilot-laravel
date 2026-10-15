<?php

namespace ParselPilot;

use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

final class ParselPilotServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/config/parsel-pilot.php',
            'parsel-pilot'
        );

        $this->app->singleton(YalidineClient::class, function ($app): YalidineClient {
            $config = $app['config']->get('parsel-pilot', []);

            if (! is_array($config)) {
                throw new InvalidArgumentException(
                    'The parsel-pilot configuration must be an array.'
                );
            }

            return new YalidineClient(
                $app->make(\Illuminate\Http\Client\Factory::class),
                $config
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            dirname(__DIR__) . '/config/parsel-pilot.php' =>
                config_path('parsel-pilot.php'),
        ], 'parsel-pilot-config');
    }
}
