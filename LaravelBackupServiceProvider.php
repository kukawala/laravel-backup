<?php
namespace Kukawala\LaravelBackup;

use Illuminate\Support\ServiceProvider;

class LaravelBackupServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        
        
        // $this->publishes([
        //     __DIR__.'/src/Config/lvbackup.php' => app()->basePath() . '/config/lvbackup.php',
        // ]);

        $this->mergeConfigFrom(
            __DIR__.'/src/Config/lvbackup.php',
            'lvbackup'
        );

        config('lvbackup');

        exit;

        //exit('aa');
        
        if ($this->app->runningInConsole()) {
            $this->commands([
                '\Kukawala\LaravelBackup\src\Commands\BackupCommands',
            ]);

            // $this->publishes([
            //     __DIR__.'/resources/views' => $this->app->resourcePath('views/vendor/mail'),
            // ], 'laravel-mail');
        }
    }
}
