<?php

namespace App\Models;

use App\Traits\HasPermissions;
use App\Traits\RefreshesPermissionCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;
use Spatie\Permission\Exceptions\RoleAlreadyExists;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Guard;

class Role extends Model implements RoleContract
{
    use HasPermissions, RefreshesPermissionCache, SoftDeletes;

    protected $guarded = ['id'];

    protected $hidden = ['pivot'];

    public static $columns = [
        'role' => [
            'table_field' => 'name',
            'sortable' => false,
            'searchable' => false,
        ]
    ];

    public function __construct(array $attributes = [])
    {
        dd('role const');
        $attributes['guard_name'] =
            $attributes['guard_name'] ?? config('auth.defaults.guard');

        parent::__construct($attributes);

        $this->setTable(config('permission.table_names.roles'));
    }

    /**
     * Find a role by its name and guard name.
     *
     * @param string      $name
     * @param string|null $guardName
     *
     * @return RoleContract|\Spatie\Permission\Models\Role
     *
     * @throws RoleDoesNotExist
     */
    public static function findByName(
        string $name,
        $guardName = null
    ): RoleContract {
        dd('test234234');
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $role = static::where('name', $name)
            ->where('guard_name', $guardName)
            ->first();
        dD("test".$role);

        if (!$role) {
            throw RoleDoesNotExist::named($name);
        }

        return $role;
    }

    public static function findById(int $id, $guardName = null): RoleContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $role = static::where('id', $id)
            ->where('guard_name', $guardName)
            ->first();

        if (!$role) {
            throw RoleDoesNotExist::withId($id);
        }

        return $role;
    }

    /**
     * Find or create role by its name (and optionally guardName).
     *
     * @param string      $name
     * @param string|null $guardName
     *
     * @return RoleContract
     */
    public static function findOrCreate(
        string $name,
        $guardName = null
    ): RoleContract {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $role = static::where('name', $name)
            ->where('guard_name', $guardName)
            ->first();

        if (!$role) {
            return self::create([
                'name' => $name,
                'guard_name' => $guardName,
            ]);
        }

        return $role;
    }

    public static function create(array $attributes = [])
    {
        $attributes['guard_name'] =
            $attributes['guard_name'] ?? Guard::getDefaultName(static::class);

        $role = static::where('name', $attributes['name'])
            ->where('guard_name', $attributes['guard_name'])
            ->first();
        if ($role) {
            throw RoleAlreadyExists::create(
                $attributes['name'],
                $attributes['guard_name']
            );
        }

        if (isNotLumen() && app()::VERSION < '5.4') {
            return parent::create($attributes);
        }

        return static::query()->create($attributes);
    }

    /**
     * A role may be given various permissions.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.permission'),
            config('permission.table_names.role_has_permissions'),
            'role_id',
            'permission_id'
        );
    }

    /**
     * A role belongs to some users of the model associated with its guard.
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(
            getModelForGuard($this->attributes['guard_name']),
            'model',
            config('permission.table_names.model_has_roles'),
            'role_id',
            config('permission.column_names.model_morph_key')
        );
    }

    /**
     * Determine if the user may perform the given permission.
     *
     * @param string|Permission $permission
     *
     * @return bool
     *
     * @throws GuardDoesNotMatch
     */
    public function hasPermissionTo($permission): bool
    {
        $permissionClass = $this->getPermissionClass();

        if (is_string($permission)) {
            $permission = $permissionClass->findByName(
                $permission,
                $this->getDefaultGuardName()
            );
        }

        if (is_int($permission)) {
            $permission = $permissionClass->findById(
                $permission,
                $this->getDefaultGuardName()
            );
        }

        if (!$this->getGuardNames()->contains($permission->guard_name)) {
            throw GuardDoesNotMatch::create(
                $permission->guard_name,
                $this->getGuardNames()
            );
        }

        return $this->permissions->contains('id', $permission->id);
    }

    public function assignPermissions($permissionIds)
    {
        $permissions = Permission::whereIn('id', $permissionIds)->get();
        if (!$this->syncPermissions($permissions)) {
            return false;
        }
        return true;
    }

    /**
     * Filter user by roles
     *
     * @param Builder $query
     * @param array   $roles
     *
     * @return Builder
     */
    public function scopeFilterByRoles(Builder $query, array $roles): Builder
    {
        return $query->when(count($roles) > 0, function ($q1) use ($roles) {
            return $q1->whereIn('name', $roles);
        });
    }

    /**
     * Check role is deletable or not
     *
     * @return bool
     */
    public function isRoleDeletable(): bool
    {
        return $this->name !== 'SuperAdmin';
    }
}
