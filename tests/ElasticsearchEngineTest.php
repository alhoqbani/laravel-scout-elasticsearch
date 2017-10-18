<?php

namespace Alhoqbani\Elastic;

use Elasticsearch\Client;
use Mockery;
use Laravel\Scout\Builder;
use Alhoqbani\Elastic\ScoutElasticEngine;
use Alhoqbani\Elastic\Fixtures\ElasticTestModel;
use Illuminate\Database\Eloquent\Collection;

class ElasticsearchEngineTest extends AbstractTestCase
{

    public function test_update_adds_objects_to_index()
    {
        $model = new ElasticTestModel;

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('bulk')->with([
            'body' => [
                [
                    'index' => [
                        '_index' => $model->searchableAs(),
                        '_type'  => $model->searchableAs(),
                        '_id'    => $model->getKey(),
                    ],
                ],
                [
                    'id' => $model->id,
                ],
            ],
        ]);

        $engine = new ScoutElasticEngine($client);
        $engine->update(Collection::make([$model]));
    }

    public function test_delete_removes_objects_to_index()
    {
        $model = new ElasticTestModel;

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('bulk')->with([
            'body' => [
                [
                    'delete' => [
                        '_index' => $model->searchableAs(),
                        '_type'  => $model->searchableAs(),
                        '_id'    => $model->getKey(),
                    ],
                ],
            ],
        ]);
        $engine = new ScoutElasticEngine($client);
        $engine->delete(Collection::make([$model]));
    }

    public function test_search_sends_correct_parameters_to_elasticsearch()
    {
        $client = Mockery::mock(Client::class);

        $client->shouldReceive('search')->with([
            'index' => 'table',
            'size'  => 15,
            'body'  => [
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
        $engine->search($builder);
    }

    public function test_search_sends_correct_correct_index_when_one_is_provided()
    {
        $client = Mockery::mock(Client::class);

        $client->shouldReceive('search')->with([
            'index' => 'custom_index',
            'size'  => 15,
            'body'  => [
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
        $builder->within('custom_index');
        $engine->search($builder);
    }

    public function test_search_with_limit()
    {
        $client = Mockery::mock(Client::class);

        $client->shouldReceive('search')->with([
            'index' => 'table',
            'size'  => '3',
            'body'  => [
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
        $builder->take('3');
        $engine->search($builder);
    }

    public function test_search_accept_closure_to_override_params()
    {
        $params = [
            'index' => 'any index',
            'body'  => [
                'query' => [
                    'match_all' => new \stdClass(),
                ],
            ],
        ];

        $client = Mockery::mock(Client::class);

        $client->shouldReceive('search')->with(\Mockery::subset($params))->once();

        $engine = new ScoutElasticEngine($client);
        $builder = new Builder(new ElasticTestModel, 'search term', function () use ($params) {
            return $params;
        });

        $engine->search($builder);
    }

    public function test_search_merge_defaults_params_with_params_from_closure()
    {
        $model = new ElasticTestModel;

        $customParamsFromClosure = [
            '_source' => ['title', 'name'],
            'body'    => [
                'query' => [
                    'match_all' => new \stdClass(),
                ],
            ],
        ];

        $expectedParams = [
            'index'   => $model->searchableAs(),
            'size'    => $model->getPerPage(),
            '_source' => $customParamsFromClosure['_source'],
            'body'    => $customParamsFromClosure['body'],
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

        $engine->search($builder);
    }

    public function test_map_correctly_maps_results_to_models()
    {
        $client = Mockery::mock(Client::class);
        $engine = new ScoutElasticEngine($client);

        $model = Mockery::mock('StdClass');
        $model->shouldReceive('getKeyName')->andReturn('id');
        $model->shouldReceive('getQualifiedKeyName')->andReturn('id');
        $model->shouldReceive('whereIn')->once()->with('id', [1])->andReturn($model);
        $model->shouldReceive('get')->once()->andReturn(Collection::make([new ElasticTestModel]));

        $results = $engine->map(
            [
                'hits' => [
                    'total' => 1,
                    'hits'  => [
                        ['_id' => 1],
                    ],
                ],
            ],
            $model
        );

        $this->assertEquals(1, count($results));
    }
}
