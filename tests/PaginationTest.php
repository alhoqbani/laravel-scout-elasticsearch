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

        $client->shouldReceive('search')->with([
            'index' => 'table',
            'type'  => 'table',
            'body'  => [
                'size'  => 13,
                'from'  => 39,
                'query' => [
                    'multi_match' => [
                        'query'  => 'search term',
                        'fields' => '_all',
                    ],
                ],
            ],
        ]);

        $engine = new ScoutElasticEngine($client);
        $builder = new Builder(new ElasticTestModel, 'search term');
        $engine->paginate($builder, 13, 4);
    }
}
