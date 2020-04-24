<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\PermissionRegistrar;
use function get_class;

trait HasRoles
{
    use HasPermissions;

    private $roleClass;

    public static function bootHasRoles()
    {
        static::deleting(
            function ($model) {
                if (method_exists($model, 'isForceDeleting')
                    && !$model->isForceDeleting()
                ) {
                    return;
                }

                $model->roles()->detach();
            }
        );
    }

    /**
     * Scope the model query to certain roles only.
     *
     * @param Builder                      $query
     * @param string|array|Role|Collection $roles
     * @param string                       $guard
     *
     * @return Builder
     */
    public function scopeRole(Builder $query, $roles, $guard = null): Builder
    {
        if ($roles instanceof Collection) {
            $roles = $roles->all();
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $roles = array_map(
            function ($role) use ($guard) {
                if ($role instanceof Role) {
                    return $role;
                }

                $method = is_numeric($role) ? 'findById' : 'findByName';
                $guard = $guard ?: $this->getDefaultGuardName();

                return $this->getRoleClass()->{$method}($role, $guard);
            }, $roles
        );

        return $query->whereHas(
            'roles', function ($query) use ($roles) {
            $query->where(
                function ($query) use ($roles) {
                    foreach ($roles as $role) {
                        $query->orWhere(
                            config('permission.table_names.roles') . '.id',
                            $role->id
                        );
                    }
                }
            );
        }
        );
    }

    public function getRoleClass()
    {
        if (!isset($this->roleClass)) {
            $this->roleClass = app(PermissionRegistrar::class)->getRoleClass();
        }

        return $this->roleClass;
    }

    /**
     * Revoke the given role from the model.
     *
     * @param string|Role $role
     *
     * @return $this
     */
    public function removeRole($role)
    {
        $this->roles()->detach($this->getStoredRole($role));

        $this->load('roles');

        return $this;
    }

    /**
     * A model may have multiple roles.
     */
    public function roles(): MorphToMany
    {
        return $this->morphToMany(
            config('permission.models.role'),
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
            'role_id'
        );
    }

    protected function getStoredRole($role): Role
    {
        $roleClass = $this->getRoleClass();

        if (is_numeric($role)) {
            return $roleClass->findById($role, $this->getDefaultGuardName());
        }

        if (is_string($role)) {
            return $roleClass->findByName($role, $this->getDefaultGuardName());
        }

        return $role;
    }

    /**
     * Remove all current roles and set the given ones.
     *
     * @param array|Role|string ...$roles
     *
     * @return $this
     */
    public function syncRoles(...$roles)
    {
        $this->roles()->detach();

        return $this->assignRole($roles);
    }

    /**
     * Assign the given role to the model.
     *
     * @param array|string|Role ...$roles
     *
     * @return $this
     */
    public function assignRole(...$roles)
    {
        $roles = collect($roles)
            ->flatten()
            ->map(
                function ($role) {
                    if (empty($role)) {
                        return false;
                    }

                    return $this->getStoredRole($role);
                }
            )
            ->filter(
                function ($role) {
                    return $role instanceof Role;
                }
            )
            ->each(
                function ($role) {
                    $this->ensureModelSharesGuard($role);
                }
            )
            ->map->id->all();

        $model = $this->getModel();

        if ($model->exists) {
            $this->roles()->sync($roles, false);
            $model->load('roles');
        } else {
            $class = get_class($model);

            $class::saved(
                function ($object) use ($roles, $model) {
                    static $modelLastFiredOn;
                    if ($modelLastFiredOn !== null
                        && $modelLastFiredOn === $model
                    ) {
                        return;
                    }
                    $object->roles()->sync($roles, false);
                    $object->load('roles');
                    $modelLastFiredOn = $object;
                }
            );
        }

        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * Determine if the model has any of the given role(s).
     *
     * @param string|array|Role|Collection $roles
     *
     * @return bool
     */
    public function hasAnyRole($roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Determine if the model has (one of) the given role(s).
     *
     * @param string|int|array|Role|Collection $roles
     *
     * @return bool
     */
    public function hasRole($roles): bool
    {
        if (is_string($roles) && false !== strpos($roles, '|')) {
            $roles = $this->convertPipeToArray($roles);
        }

        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }

        if (is_int($roles)) {
            return $this->roles->contains('id', $roles);
        }

        if ($roles instanceof Role) {
            return $this->roles->contains('id', $roles->id);
        }

        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }

            return false;
        }

        return $roles->intersect($this->roles)->isNotEmpty();
    }

    protected function convertPipeToArray(string $pipeString)
    {
        $pipeString = trim($pipeString);

        if (strlen($pipeString) <= 2) {
            return $pipeString;
        }

        $quoteCharacter = substr($pipeString, 0, 1);
        $endCharacter = substr($quoteCharacter, -1, 1);

        if ($quoteCharacter !== $endCharacter) {
            return explode('|', $pipeString);
        }

        if (!in_array($quoteCharacter, ["'", '"'])) {
            return explode('|', $pipeString);
        }

        return explode('|', trim($pipeString, $quoteCharacter));
    }

    /**
     * Determine if the model has all of the given role(s).
     *
     * @param string|Role|Collection $roles
     *
     * @return bool
     */
    public function hasAllRoles($roles): bool
    {
        if (is_string($roles) && false !== strpos($roles, '|')) {
            $roles = $this->convertPipeToArray($roles);
        }

        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }

        if ($roles instanceof Role) {
            return $this->roles->contains('id', $roles->id);
        }

        $roles = collect()
            ->make($roles)
            ->map(
                function ($role) {
                    return $role instanceof Role ? $role->name : $role;
                }
            );

        return $roles->intersect($this->getRoleNames()) == $roles;
    }

    public function getRoleNames(): Collection
    {
        return $this->roles->pluck('name');
    }

    /**
     * Return all permissions directly coupled to the model.
     */
    public function getDirectPermissions(): Collection
    {
        return $this->permissions;
    }
}