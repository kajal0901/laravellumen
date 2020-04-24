<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class BaseRepository implements BaseContract
{
    /**
     * The repository associated main model.
     *
     * @var Model
     */
    protected $model;

    protected $columns = ['*'];
    protected $events = [];
    protected $with = [];

    protected $withByMethods = [
        'getAll' => [],
        'find' => [],
    ];

    /**
     * BaseRepository constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records
     *
     * @param array|null  $filter
     * @param string|null $orderBy
     * @param string|null $orderByDirection
     *
     * @return Collection
     */
    public function getAll(
        ?array $filter = [],
        ?string $orderBy = 'default',
        ?string $orderByDirection = 'asc'
    ): Collection
    {
        $orderBy =
            $orderBy === 'default' ? $this->model->getKeyName() : $orderBy;
        return $this->model
            ->with($this->withByMethods['getAll'])
            ->orderBy($orderBy, $orderByDirection)
            ->get($this->columns);
    }

    /**
     * Get all
     *
     * @param array $filter
     *
     * @return LengthAwarePaginator
     */
    public function paginate(array $filter = []): LengthAwarePaginator
    {
        return $this->model
            ->with($this->withByMethods['getAll'])
            ->orderBy('id', 'desc')
            ->paginate(isset($filter['perPage']) ? $filter['perPage'] : 20);
    }

    /**
     * Edit record by id
     *
     * @param  $id
     *
     * @return Model|null
     * @throws ModelNotFoundException
     */
    public function edit(int $id): Model
    {
        return $this->find($id);
    }

    /**
     * Find record by id
     *
     * @param  $id
     *
     * @return Model
     * @throws ModelNotFoundException
     */
    public function find(int $id): Model
    {
        return $this->model
            ->with($this->withByMethods['find'])
            ->select($this->columns)
            ->findOrFail($id);
    }

    /**
     * Delete record
     *
     * @param  $id
     *
     * @return mixed
     */
    public function delete(int $id)
    {
        return $this->model->findOrFail($id)->delete();
    }

    /**
     * Store
     *
     * @param array $input
     *
     * @return mixed
     */
    public function store(array $input)
    {
        return $this->model->create($input);
    }

    /**
     * Update
     *
     * @param       $id
     * @param array $input
     *
     * @return mixed
     */
    public function update(int $id, array $input)
    {
        $model = $this->find($id);
        $model->fill($input);
        $model->save();
        return $model;
    }
}
