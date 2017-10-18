<?php

namespace Alhoqbani\Elastic;

use Elasticsearch\Client;
use Mockery;
use Laravel\Scout\Builder;
use Alhoqbani\Elastic\ScoutElasticEngine;
use Alhoqbani\Elastic\Fixtures\ElasticTestModel;
use Illuminate\Database\Eloquent\Collection;

class PaginationTest extends AbstractTestCase
{

    public function test_paginate_will_send_correct_size_and_from_params()
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('search')->with(\Mockery::subset([
            'body' => [
                'size' => 13,
                'from' => 39,
            ],
        ]));

        $engine = new ScoutElasticEngine($client);
        $builder = new Builder(new ElasticTestModel, 'search term');
        $engine->paginate($builder, 13, 4);
    }

    public function test_paginate_will_send_correct_index_and_type_params()
    {
        $model = new ElasticTestModel;
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('search')->with(\Mockery::subset([
            'index' => $model->searchableAs(),
            'type'  => $model->searchableAs(),
        ]));

        $engine = new ScoutElasticEngine($client);
        $builder = new Builder($model, 'search term');
        $engine->paginate($builder, 13, 4);
    }

    public function test_paginate_will_send_correct_index_and_type_params_when_custom_index_is_provided()
    {
        $model = new ElasticTestModel;
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('search')->with(\Mockery::subset([
            'index' => 'custom_index',
            'type'  => 'custom_index',
        ]));

        $engine = new ScoutElasticEngine($client);
        $builder = new Builder($model, 'search term');
        $builder->within('custom_index');
        $engine->paginate($builder, 10, 1);
    }
}
