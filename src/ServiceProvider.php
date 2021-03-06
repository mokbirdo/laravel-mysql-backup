<?php

namespace Mokbirdo\LaravelMysqlBackup;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Mokbirdo\LaravelMysqlBackup\Console\Commands\LaravelDbBackup;

class ServiceProvider extends LaravelServiceProvider
{
    public function __construct($app)
    {
        parent::__construct($app);
        // Dev autoload
        $autoload_path = str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/../vendor/autoload.php');
        if (file_exists($autoload_path)) {
            require($autoload_path);
        }
    }

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->handleConfigs();
        // $this->handleMigrations();
        // $this->handleViews();
        // $this->handleTranslations();
        // $this->handleRoutes();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Bind any implementations.
        $this->commands([
            LaravelDbBackup::class
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function handleConfigs()
    {
        $configPath = __DIR__ . '/../config/packagename.php';

//        $this->publishes([$configPath => config_path('packagename.php')]);
//
//        $this->mergeConfigFrom($configPath, 'packagename');
    }

    private function handleTranslations()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'packagename');
    }
}
