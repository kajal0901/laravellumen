<?php

namespace App\Traits;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Guard;
use Spatie\Permission\PermissionRegistrar;
use function get_class;

trait HasPermissions
{
    private $permissionClass;

    public static function bootHasPermissions(): void
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting')
                && !$model->isForceDeleting()
            ) {
                return;
            }

            $model->permissions()->detach();
        });
    }

    /**
     * Scope the model query to certain permissions only.
     *
     * @param Builder                            $query
     * @param string|array|Permission|Collection $permissions
     *
     * @return Builder
     */
    public function scopePermission(Builder $query, $permissions): Builder
    {
        $permissions = $this->convertToPermissionModels($permissions);

        $rolesWithPermissions = array_unique(
            array_reduce(
                $permissions,
                function ($result, $permission) {
                    return array_merge($result, $permission->roles->all());
                },
                []
            )
        );

        return $query->where(
            function ($query) use (
                $permissions,
                $rolesWithPermissions
            ) {
                $query->whereHas('permissions', function ($query) use ($permissions) {
                    $query->where(
                        function ($query) use ($permissions) {
                            foreach ($permissions as $permission) {
                                $query->orWhere(
                                    config('permission.table_names.permissions') .
                                    '.id',
                                    $permission->id
                                );
                            }
                        }
                    );
                });
                if (count($rolesWithPermissions) > 0) {
                    $query->orWhereHas('roles', function ($query) use ($rolesWithPermissions) {
                        $query->where(
                            function ($query) use (
                                $rolesWithPermissions
                            ) {
                                foreach ($rolesWithPermissions as $role) {
                                    $query->orWhere(
                                        config('permission.table_names.roles') . '.id',
                                        $role->id
                                    );
                                }
                            }
                        );
                    });
                }
            }
        );
    }

    /**
     * @param string|array|Permission|Collection $permissions
     *
     * @return array
     */
    protected function convertToPermissionModels($permissions): array
    {
        if ($permissions instanceof Collection) {
            $permissions = $permissions->all();
        }

        $permissions = is_array($permissions) ? $permissions : [$permissions];

        return array_map(
            function ($permission) {
                if ($permission instanceof Permission) {
                    return $permission;
                }
                return $this->getPermissionClass()->findByName(
                    $permission,
                    $this->getDefaultGuardName()
                );
            },
            $permissions
        );
    }

    public function getPermissionClass()
    {
        if (!isset($this->permissionClass)) {
            $this->permissionClass = app(
                PermissionRegistrar::class
            )->getPermissionClass();
        }

        return $this->permissionClass;
    }

    protected function getDefaultGuardName(): string
    {
        return Guard::getDefaultName($this);
    }

    /**
     * @deprecated since 2.35.0
     * @alias      of hasPermissionTo()
     */
    public function hasUncachedPermissionTo(
        $permission,
        $guardName = null
    ): bool
    {
        return $this->hasPermissionTo($permission, $guardName);
    }

    /**
     * Determine if the model may perform the given permission.
     *
     * @param string|int|Permission $permission
     * @param string|null           $guardName
     *
     * @return bool
     * @throws PermissionDoesNotExist
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        $permissionClass = $this->getPermissionClass();

        if (is_string($permission)) {
            $permission = $permissionClass->findByName(
                $permission,
                $guardName ?? $this->getDefaultGuardName()
            );
        }

        if (is_int($permission)) {
            $permission = $permissionClass->findById(
                $permission,
                $guardName ?? $this->getDefaultGuardName()
            );
        }

        if (!$permission instanceof Permission) {
            throw new PermissionDoesNotExist();
        }

        return $this->hasDirectPermission($permission) ||
            $this->hasPermissionViaRole($permission);
    }

    /**
     * Determine if the model has the given permission.
     *
     * @param string|int|Permission $permission
     *
     * @return bool
     */
    public function hasDirectPermission($permission): bool
    {
        $permissionClass = $this->getPermissionClass();

        if (is_string($permission)) {
            $permission = $permissionClass->findByName(
                $permission,
                $this->getDefaultGuardName()
            );
            if (!$permission) {
                return false;
            }
        }

        if (is_int($permission)) {
            $permission = $permissionClass->findById(
                $permission,
                $this->getDefaultGuardName()
            );
            if (!$permission) {
                return false;
            }
        }

        if (!$permission instanceof Permission) {
            return false;
        }

        return $this->permissions->contains('id', $permission->id);
    }

    /**
     * Determine if the model has, via roles, the given permission.
     *
     * @param Permission $permission
     *
     * @return bool
     */
    protected function hasPermissionViaRole(Permission $permission): bool
    {
        return $this->hasRole($permission->roles);
    }

    /**
     * Determine if the model has any of the given permissions.
     *
     * @param array ...$permissions
     *
     * @return bool
     * @throws Exception
     */
    public function hasAnyPermission(...$permissions): bool
    {
        if (is_array($permissions[0])) {
            $permissions = $permissions[0];
        }

        foreach ($permissions as $permission) {
            if ($this->checkPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * An alias to hasPermissionTo(), but avoids throwing an exception.
     *
     * @param string|int|Permission $permission
     * @param string|null           $guardName
     *
     * @return bool
     */
    public function checkPermissionTo($permission, $guardName = null): bool
    {
        try {
            return $this->hasPermissionTo($permission, $guardName);
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }

    /**
     * Determine if the model has all of the given permissions.
     *
     * @param array ...$permissions
     *
     * @return bool
     * @throws Exception
     */
    public function hasAllPermissions(...$permissions): bool
    {
        if (is_array($permissions[0])) {
            $permissions = $permissions[0];
        }

        foreach ($permissions as $permission) {
            if (!$this->hasPermissionTo($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return all the permissions the model has, both directly and via roles.
     *
     * @throws Exception
     */
    public function getAllPermissions(): Collection
    {
        $permissions = $this->permissions;

        if ($this->roles) {
            $permissions = $permissions->merge($this->getPermissionsViaRoles());
        }

        return $permissions->sort()->values();
    }

    /**
     * Return all the permissions the model has via roles.
     */
    public function getPermissionsViaRoles(): Collection
    {
        return $this->load('roles', 'roles.permissions')
            ->roles->flatMap(
                function ($role) {
                    return $role->permissions;
                }
            )
            ->sort()
            ->values();
    }

    /**
     * Remove all current permissions and set the given ones.
     *
     * @param string|array|Permission|Collection $permissions
     *
     * @return $this
     */
    public function syncPermissions(...$permissions): self
    {
        $this->permissions()->detach();

        return $this->givePermissionTo($permissions);
    }

    /**
     * A model may have multiple direct permissions.
     */
    public function permissions(): MorphToMany
    {
        return $this->morphToMany(
            config('permission.models.permission'),
            'model',
            config('permission.table_names.model_has_permissions'),
            config('permission.column_names.model_morph_key'),
            'permission_id'
        );
    }

    /**
     * Grant the given permission(s) to a role.
     *
     * @param string|array|Permission|Collection $permissions
     *
     * @return $this
     */
    public function givePermissionTo(...$permissions): self
    {
        $permissions = collect($permissions)
            ->flatten()
            ->map(
                function ($permission) {
                    if (empty($permission)) {
                        return false;
                    }

                    return $this->getStoredPermission($permission);
                }
            )
            ->filter(
                function ($permission) {
                    return $permission instanceof Permission;
                }
            )
            ->each(
                function ($permission) {
                    $this->ensureModelSharesGuard($permission);
                }
            )
            ->map->id->all();

        $model = $this->getModel();

        if ($model->exists) {
            $this->permissions()->sync($permissions, false);
            $model->load('permissions');
        } else {
            $class = get_class($model);

            $class::saved(
                function ($object) use ($permissions, $model) {
                    static $modelLastFiredOn;
                    if ($modelLastFiredOn !== null
                        && $modelLastFiredOn === $model
                    ) {
                        return;
                    }
                    $object->permissions()->sync($permissions, false);
                    $object->load('permissions');
                    $modelLastFiredOn = $object;
                }
            );
        }

        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * @param string|array|Permission|Collection $permissions
     *
     * @return Permission|Permission[]|Collection
     */
    protected function getStoredPermission($permissions)
    {
        $permissionClass = $this->getPermissionClass();

        if (is_numeric($permissions)) {
            return $permissionClass->findById(
                $permissions,
                $this->getDefaultGuardName()
            );
        }

        if (is_string($permissions)) {
            return $permissionClass->findByName(
                $permissions,
                $this->getDefaultGuardName()
            );
        }

        if (is_array($permissions)) {
            return $permissionClass
                ->whereIn('name', $permissions)
                ->whereIn('guard_name', $this->getGuardNames())
                ->get();
        }

        return $permissions;
    }

    protected function getGuardNames(): Collection
    {
        return Guard::getNames($this);
    }

    /**
     * @param Permission|Role $roleOrPermission
     *
     * @throws GuardDoesNotMatch
     */
    protected function ensureModelSharesGuard($roleOrPermission): void
    {
        if (!$this->getGuardNames()->contains($roleOrPermission->guard_name)) {
            throw GuardDoesNotMatch::create(
                $roleOrPermission->guard_name,
                $this->getGuardNames()
            );
        }
    }

    /**
     * Forget the cached permissions.
     */
    public function forgetCachedPermissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Revoke the given permission.
     *
     * @param Permission|Permission[]|string|string[] $permission
     *
     * @return $this
     */
    public function revokePermissionTo($permission): self
    {
        $this->permissions()->detach($this->getStoredPermission($permission));

        $this->forgetCachedPermissions();

        $this->load('permissions');

        return $this;
    }

    public function getPermissionNames(): Collection
    {
        return $this->permissions->pluck('name');
    }
}
