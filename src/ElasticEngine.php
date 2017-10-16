<?php

namespace Alhoqbani\Elastic;

use Elasticsearch\Client;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;

class ElasticEngine extends Engine
{

    /**
     * The Elasticsearch client.
     *
     * @var \Elasticsearch\Client
     */
    private $client;

    /**
     * ElasticSearchScoutEngine constructor.
     *
     * @param \Elasticsearch\Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Update the given model in the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection $models
     *
     * @return void
     */
    public function update($models)
    {
        $index = $models->first()->searchableAs();

        $params = ['body' => []];
        foreach ($models as $model) {
            $array = $model->toSearchableArray();

            if (empty($array)) {
                return;
            }

            $params['body'][] = [
                'index' => [
                    '_index' => $index,
                    '_type'  => $index,
                    '_id'    => $model->getKey(),
                ],
            ];
            $params['body'][] = $array;
        }

        $this->client->bulk($params);
    }

    /**
     * Remove the given model from the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection $models
     *
     * @return void
     */
    public function delete($models)
    {
        $index = $models->first()->searchableAs();

        $params = ['body' => []];
        foreach ($models as $model) {
            $params['body'][] = [
                'delete' => [
                    '_index' => $index,
                    '_type'  => $index,
                    '_id'    => $model->getKey(),
                ],
            ];
        }

        $this->client->bulk($params);
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder $builder
     *
     * @return mixed
     */
    public function search(Builder $builder)
    {
        if ($builder->callback) {
            return call_user_func(
                $builder->callback,
                $this->client,
                $builder->query
            );
        }

        return $this->client->search([
            'index' => $builder->index ?? $builder->model->searchableAs(),
            'type'  => $builder->index ?? $builder->model->searchableAs(),
            'body'  => [
                'size'  => $builder->limit ?? $builder->model->getPerPage(),
                'query' => [
                    'multi_match' => [
                        'query'  => $builder->query,
                        'fields' => '_all',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder $builder
     * @param  int                    $perPage
     * @param  int                    $page
     *
     * @return mixed
     */
    public function paginate(Builder $builder, $perPage, $page)
    {
        return $this->client->search([
            'index' => $builder->index ?? $builder->model->searchableAs(),
            'type'  => $builder->index ?? $builder->model->searchableAs(),
            'body'  => [
                'size'  => $perPage,
                'from'  => ($page - 1) * $perPage,
                'query' => [
                    'multi_match' => [
                        'query'  => $builder->query,
                        'fields' => '_all',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Pluck and return the primary keys of the given results.
     *
     * @param  mixed $results
     *
     * @return \Illuminate\Support\Collection
     */
    public function mapIds($results)
    {
        return collect($results['hits']['hits'])->pluck('_id')->values();
    }

    /**
     * Map the given results to instances of the given model.
     *
     * @param  mixed                               $results
     * @param  \Illuminate\Database\Eloquent\Model $model
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function map($results, $model)
    {
        if ($results['hits']['total'] === 0) {
            return Collection::make();
        }

        $keys = collect($results['hits']['hits'])
            ->pluck('_id')->values()->all();

        $models = $model->whereIn(
            $model->getQualifiedKeyName(),
            $keys
        )->get()->keyBy($model->getKeyName());

        return Collection::make($results['hits']['hits'])->map(function ($hit) use ($model, $models) {
            $key = $hit['_id'];

            if (isset($models[$key])) {
                return $models[$key];
            }

        })->filter()->values();
    }

    /**
     * Get the total count from a raw result returned by the engine.
     *
     * @param  mixed $results
     *
     * @return int
     */
    public function getTotalCount($results)
    {
        return $results['hits']['total'];
    }
}
