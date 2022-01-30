<?php

namespace Anacreation\Modules;

use App\Console\Kernel;
use Illuminate\Console\Application as Artisan;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class ModulesServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        $this->mergeConfigFrom(__DIR__."/../config/config.php",'anamodules');

        $namespace = "Anacreation\\Modules\\";

        foreach ((new Finder)->in(__DIR__ . '/Commands')->files() as $command) {

            $command = $namespace . str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    Str::after($command->getRealPath(), realpath(__DIR__) . DIRECTORY_SEPARATOR)
                );

            Artisan::starting(function ($artisan) use ($command) {
                $artisan->resolve($command);
            });
        }


    }
}
