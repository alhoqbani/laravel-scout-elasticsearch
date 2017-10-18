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
            'size' => 13,
            'from' => 39,
        ]));

        $engine = new ScoutElasticEngine($client);
        $builder = new Builder(new ElasticTestModel, 'search term');
        $engine->paginate($builder, 13, 4);
    }

    public function test_paginate_will_send_correct_index_param()
    {
        $model = new ElasticTestModel;
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('search')->with(\Mockery::subset([
            'index' => $model->searchableAs(),
        ]));

        $engine = new ScoutElasticEngine($client);
        $builder = new Builder($model, 'search term');
        $engine->paginate($builder, 13, 4);
    }

    public function test_paginate_will_send_correct_index_param_when_custom_index_is_provided()
    {
        $model = new ElasticTestModel;
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('search')->with(\Mockery::subset([
            'index' => 'custom_index',
        ]));

        $engine = new ScoutElasticEngine($client);
        $builder = new Builder($model, 'search term');
        $builder->within('custom_index');
        $engine->paginate($builder, 10, 1);
    }

    public function test_paginate_will_send_correct_params_when_search_by_closure()
    {
        $model = new ElasticTestModel;

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('search')->withArgs(function ($args) {
            return $args['size'] == 10 && $args['from'] == 0;
        });

        $engine = new ScoutElasticEngine($client);
        $builder = new Builder($model, 'search term', function () {
            return [
                'body' => [
                    'query' => [
                        'custom_query',
                    ],
                ],
            ];
        });

        $engine->paginate($builder, 10, 1);
    }

    public function test_paginate_will_merge_defaults_params_with_params_from_closure()
    {
        $model = new ElasticTestModel;

        $customParamsFromClosure = [
            '_source' => ['title', 'name'],
            'size'    => 'will be ignored size',
            'from'    => 'will be ignored from',
            'body'    => [
                'size'  => 'from should be removed from params',
                'from'  => 'size should be removed from params',
                'query' => [
                    'match_all' => new \stdClass(),
                ],
            ],
        ];

        $expectedParams = [
            'index'   => $model->searchableAs(),
            'size'    => 10,
            'from'    => 0,
            '_source' => $customParamsFromClosure['_source'],
            'body'    => [
                'query' => [
                    'match_all' => new \stdClass(),
                ],
            ],
        ];

        $client = Mockery::mock(Client::class);

        $client->shouldReceive('search')->withArgs(function ($params) use ($expectedParams) {
            $this->assertEquals($expectedParams, $params);

            return true;
        });

        $engine = new ScoutElasticEngine($client);
        $builder = new Builder($model, 'search term', function () use ($customParamsFromClosure) {
            return $customParamsFromClosure;
        });

        $engine->paginate($builder, 10, 1);
    }

}
