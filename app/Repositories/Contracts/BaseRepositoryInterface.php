<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface BaseRepositoryInterface
{
    public function query(bool $onlyActive = true): Builder;

    public function all(array $columns = ['*'], array $relations = []): Collection;

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator;

    public function find(int|string $id, array $columns = ['*'], array $relations = []): ?Model;

    public function findOrFail(int|string $id, array $columns = ['*'], array $relations = []): Model;

    public function findBy(string $column, mixed $value, array $columns = ['*'], array $relations = []): ?Model;

    public function getBy(string $column, mixed $value, array $columns = ['*'], array $relations = []): Collection;

    public function create(array $data): Model;

    public function update(int|string|Model $model, array $data): Model;

    public function delete(int|string|Model $model, ?int $deletedBy = null): bool;

    public function restore(int|string|Model $model): bool;
}
