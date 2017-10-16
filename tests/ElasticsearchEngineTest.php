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
            'type'  => 'table',
            'body'  => [
                'size'  => 15,
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
//        $builder->where('foo', 1);
        $engine->search($builder);
    }

    public function test_search_sends_correct_correct_index_when_one_is_provided()
    {
        $client = Mockery::mock(Client::class);

        $client->shouldReceive('search')->with([
            'index' => 'custom_index',
            'type'  => 'custom_index',
            'body'  => [
                'size'  => 15,
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
            'type'  => 'table',
            'body'  => [
                'size'  => '3',
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
