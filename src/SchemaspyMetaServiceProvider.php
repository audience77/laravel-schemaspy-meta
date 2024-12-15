<?php

namespace Audience77\LaravelSchemaspyMeta;

use Illuminate\Support\ServiceProvider;

class SchemaspyMetaServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            'command.schemaspy-meta:generate',
            function ($app) {
                return new GenerateSchemaMeta();
            }
        );

        $this->commands(
            'command.schemaspy-meta:generate'
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('command.schemaspy-meta:generate');
    }
}
