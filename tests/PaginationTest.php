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
                'from' => 39
            ]
        ]));

        $engine = new ScoutElasticEngine($client);
        $builder = new Builder(new ElasticTestModel, 'search term');
        $engine->paginate($builder, 13, 4);
    }
}
