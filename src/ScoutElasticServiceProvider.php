<?php

namespace Alhoqbani\Elastic;

use Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;

class ScoutElasticServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $this->app->make(EngineManager::class)->extend('elastic', function () {

            $hosts = $this->app['config']->get('services.scout-elastic.hosts');

            $client = ClientBuilder::create()
                ->setHosts($hosts)
                ->build();

            return new ScoutElasticEngine($client);
        });

        $this->publishes([
            __DIR__ . '/../config/scout-elastic.php' =>
                $this->app['path.config'] . DIRECTORY_SEPARATOR . 'scout-elastic.php',
        ]);
    }
}
