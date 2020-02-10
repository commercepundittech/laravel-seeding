<?php

namespace CommercePunditTech\LaravelSeeding;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class LaravelSeedingServiceProvider extends ServiceProvider
{

    public function boot(Filesystem $filesystem)
    {
        $this->publishes([
            __DIR__ . '/../config/laravelseeding.php' => config_path('laravelseeding.php'),
        ], 'config');
        $this->publishes([
            __DIR__ . '/stubs/database/migrations/create_seeder_table.php.stub' => $this->getMigrationFileName($filesystem),
        ], 'migrations');

        $this->commands([
            Commands\SeederMakeCommand::class,
            Commands\DbSeedCommand::class,
        ]);
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @param Filesystem $filesystem
     * @return string
     */
    protected function getMigrationFileName(Filesystem $filesystem): string
    {
        $timestamp = date('Y_m_d_His');
        return Collection::make($this->app->databasePath() . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem) {
                return $filesystem->glob($path . '*_create_laravelseeding_tables.php');
            })->push($this->app->databasePath() . "/migrations/{$timestamp}_create_laravelseeding_tables.php")
            ->first();
    }


    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravelseeding.php',
            'laravelseeding'
        );

    }

}
