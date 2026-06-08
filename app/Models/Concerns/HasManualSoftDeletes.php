<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

trait HasManualSoftDeletes
{
    protected static string $manualSoftDeleteScope = 'manual_soft_deletes';

    /**
     * @var array<string, array<string, bool>>
     */
    protected static array $manualSoftDeleteColumnCache = [];

    public static function bootHasManualSoftDeletes(): void
    {
        static::addGlobalScope(static::$manualSoftDeleteScope, function (Builder $builder): void {
            $builder->where($builder->getModel()->qualifyColumn('delete_status'), false);
        });

        static::creating(function (Model $model): void {
            if (!$model instanceof self) {
                return;
            }

            $model->syncAuditColumn('created_by');
            $model->syncAuditColumn('updated_by');

            if ($model->supportsColumn('delete_status') && $model->getAttribute('delete_status') === null) {
                $model->setAttribute('delete_status', false);
            }
        });

        static::updating(function (Model $model): void {
            if (!$model instanceof self) {
                return;
            }

            $model->syncAuditColumn('updated_by');
        });
    }

    public function scopeWithInactive(Builder $query): Builder
    {
        return $query->withoutGlobalScope(static::$manualSoftDeleteScope);
    }

    public function scopeOnlyInactive(Builder $query): Builder
    {
        return $query
            ->withoutGlobalScope(static::$manualSoftDeleteScope)
            ->where($this->qualifyColumn('delete_status'), true);
    }

    public function supportsManualSoftDelete(): bool
    {
        return true;
    }

    public function markAsDeleted(?int $deletedBy = null): bool
    {
        $this->setAttribute('delete_status', true);

        if ($this->supportsColumn('deleted_at')) {
            $this->setAttribute('deleted_at', now());
        }

        if ($this->supportsColumn('deleted_by')) {
            $this->setAttribute('deleted_by', $deletedBy ?? $this->currentUserId());
        }

        return $this->save();
    }

    public function restoreManualDelete(): bool
    {
        $this->setAttribute('delete_status', false);

        if ($this->supportsColumn('deleted_at')) {
            $this->setAttribute('deleted_at', null);
        }

        if ($this->supportsColumn('deleted_by')) {
            $this->setAttribute('deleted_by', null);
        }

        return $this->save();
    }

    protected function syncAuditColumn(string $column): void
    {
        if (!$this->supportsColumn($column)) {
            return;
        }

        if ($this->getAttribute($column) !== null) {
            return;
        }

        $userId = $this->currentUserId();

        if ($userId !== null) {
            $this->setAttribute($column, $userId);
        }
    }

    protected function supportsColumn(string $column): bool
    {
        $modelClass = static::class;

        if (!isset(static::$manualSoftDeleteColumnCache[$modelClass][$column])) {
            static::$manualSoftDeleteColumnCache[$modelClass][$column] = Schema::hasColumn($this->getTable(), $column);
        }

        return static::$manualSoftDeleteColumnCache[$modelClass][$column];
    }

    protected function currentUserId(): ?int
    {
        $authId = auth()->id();

        return is_int($authId) ? $authId : null;
    }
}
