<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface BaseContract
{
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
        ?array $filter,
        ?string $orderBy,
        ?string $orderByDirection
    ): Collection;

    /**
     * Get All users
     *
     * @param array $filter
     *
     * @return LengthAwarePaginator
     */
    public function paginate(array $filter = []): LengthAwarePaginator;

    /**
     * Find record by id
     *
     * @param  $id
     *
     * @return Model
     * @throws ModelNotFoundException
     */
    public function find(int $id): Model;

    /**
     * Edit record by id
     *
     * @param  $id
     *
     * @return Model|null
     * @throws ModelNotFoundException
     */
    public function edit(int $id): Model;

    /**
     * Delete user
     *
     * @param  $id
     *
     * @return mixed
     */
    public function delete(int $id);

    /**
     * Create new user
     *
     * @param array $input
     *
     * @return mixed
     */
    public function store(array $input);

    /**
     * Update user
     *
     * @param       $id
     * @param array $input
     *
     * @return mixed
     */
    public function update(int $id, array $input);
}