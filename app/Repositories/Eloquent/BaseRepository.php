<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var array<int, string>
     */
    protected array $systemWriteProtectedAttributes = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'updated_by',
        'deleted_by',
        'delete_status',
    ];

    public function __construct(protected Model $model)
    {
    }

    public function query(bool $onlyActive = true): Builder
    {
        $query = $this->model->newQuery();

        if ($onlyActive) {
            return $this->applyOnlyActiveScope($query);
        }

        if ($this->supportsManualSoftDeleteTrait()) {
            return $query->withoutGlobalScope('manual_soft_deletes');
        }

        return $query;
    }

    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->withRelations($this->query(true), $relations)->get($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->withRelations($this->query(true), $relations)->paginate($perPage, $columns);
    }

    public function find(int|string $id, array $columns = ['*'], array $relations = []): ?Model
    {
        return $this->withRelations($this->query(true), $relations)->find($id, $columns);
    }

    public function findOrFail(int|string $id, array $columns = ['*'], array $relations = []): Model
    {
        return $this->withRelations($this->query(true), $relations)->findOrFail($id, $columns);
    }

    public function findBy(string $column, mixed $value, array $columns = ['*'], array $relations = []): ?Model
    {
        return $this->withRelations($this->query(true), $relations)->where($column, $value)->first($columns);
    }

    public function getBy(string $column, mixed $value, array $columns = ['*'], array $relations = []): Collection
    {
        return $this->withRelations($this->query(true), $relations)->where($column, $value)->get($columns);
    }

    public function create(array $data): Model
    {
        return $this->model->newQuery()->create($this->sanitizeMassAssignmentData($data));
    }

    public function update(int|string|Model $model, array $data): Model
    {
        $entity = $this->resolveModel($model, false);
        $entity->fill($this->sanitizeMassAssignmentData($data));
        $entity->save();

        return $entity->refresh();
    }

    public function delete(int|string|Model $model, ?int $deletedBy = null): bool
    {
        $entity = $this->resolveModel($model, false);

        if (!$this->supportsManualSoftDelete()) {
            return (bool) $entity->delete();
        }

        if ($this->supportsManualSoftDeleteTrait() && method_exists($entity, 'markAsDeleted')) {
            /** @var bool $deleted */
            $deleted = $entity->markAsDeleted($deletedBy);

            return $deleted;
        }

        $entity->setAttribute('delete_status', true);

        return $entity->save();
    }

    public function restore(int|string|Model $model): bool
    {
        if (!$this->supportsManualSoftDelete()) {
            return false;
        }

        $entity = $this->resolveModel($model, false);

        if ($this->supportsManualSoftDeleteTrait() && method_exists($entity, 'restoreManualDelete')) {
            /** @var bool $restored */
            $restored = $entity->restoreManualDelete();

            return $restored;
        }

        $entity->setAttribute('delete_status', false);

        return $entity->save();
    }

    protected function resolveModel(int|string|Model $model, bool $onlyActive = false): Model
    {
        if ($model instanceof Model) {
            return $model;
        }

        return $this->query($onlyActive)->findOrFail($model);
    }

    protected function withRelations(Builder $query, array $relations = []): Builder
    {
        if (empty($relations)) {
            return $query;
        }

        return $query->with($relations);
    }

    protected function supportsManualSoftDelete(): bool
    {
        return $this->supportsManualSoftDeleteTrait() || $this->hasDeleteStatusAttribute();
    }

    protected function supportsManualSoftDeleteTrait(): bool
    {
        if (!method_exists($this->model, 'supportsManualSoftDelete')) {
            return false;
        }

        $supports = $this->model->supportsManualSoftDelete();

        return is_bool($supports) ? $supports : false;
    }

    protected function hasDeleteStatusAttribute(): bool
    {
        return in_array('delete_status', $this->model->getFillable(), true);
    }

    protected function applyOnlyActiveScope(Builder $query): Builder
    {
        if ($this->supportsManualSoftDeleteTrait()) {
            return $query;
        }

        if ($this->hasDeleteStatusAttribute()) {
            $query->where($this->model->qualifyColumn('delete_status'), false);
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function sanitizeMassAssignmentData(array $data): array
    {
        return Arr::except($data, $this->systemWriteProtectedAttributes);
    }
}
