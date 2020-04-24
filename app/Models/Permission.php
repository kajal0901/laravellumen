<?php

namespace App\Models;

use App\Traits\HasRoles;
use App\Traits\RefreshesPermissionCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Exceptions\PermissionAlreadyExists;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Guard;
use Spatie\Permission\PermissionRegistrar;

/**
 * @method static Builder whereNotIn(string $string, array $array_keys)
 */
class Permission extends Model implements PermissionContract
{
    use HasRoles, RefreshesPermissionCache, SoftDeletes;

    public static $shortcutNames = [
        'index' => 'View all %s',
        'incoming' => 'Incoming licenses',
        'machine_list' => 'Machine list',
        'licenseShow' => 'License detail view',
        'superAdminShow' => 'SuperAdmin detail view',
        'superAdminIndex' => 'SuperAdmin',
        'supportIndex' => 'Support',
        'supportShow' => 'Support detail view',
        'licenseIndex' => 'License',
        'licenseUpdate' => 'Customer license update',
        'license_numbers' => 'License numbers',
        'machineIndex' => 'Customer machines',
        'machineStore' => 'Create customer machine',
        'machineShow' => 'View customer machine',
        'machineUpdate' => 'Update customer machine',
        'licenseStore' => 'Create customer license',
        'incomingRequestList' => 'Incoming license requests',
        'incomingAssignCustomer' => 'Assign Customer to machine',
        'sendEmail' => 'Send license report',
        'moduleList' => 'Manage modules',
        'moduleStore' => 'Module save',
        'moduleUpdate' => 'Module update',
        'show' => 'View %s',
        'store' => 'Create %s',
        'destroy' => 'Delete %s',
        'update' => 'Update %s',
        'generate' => 'Generate %s',
        'byModule' => 'Module wise view %s',
        'userStatistics' => 'Statistics %s',
        'archive' => 'Archive %s',
        'restore' => 'Restore %s',
        'timeline' => 'View %s\'s Timeline',
    ];
    public static $singularNames = [
        'show',
        'store',
        'destroy',
        'update',
        'archive',
        'restore',
        'timeline',
    ];
    public static $exceptMenuBackList = [
        'destroy',
        'show',
        'update',
        'restore',
        'generate',
        'byModule',
        'timeline',
        'licenseUpdate',
        'sendEmail',
        'moduleList',
        'moduleStore',
        'moduleUpdate',
        'licenseStore',
        'machineShow',
        'superAdminShow',
        'licenseShow',
        'supportShow',
        'incomingAssignCustomer',
    ];
    public static $exceptMenuModule = ['Widgets', 'CustomerLicense'];
    protected $guarded = ['id'];
    protected $hidden = ['pivot'];

    public function __construct(array $attributes = [])
    {

        $attributes['guard_name'] =
            $attributes['guard_name'] ?? config('auth.defaults.guard');

        parent::__construct($attributes);

        $this->setTable(config('permission.table_names.permissions'));
    }

    /**
     * Find a permission by its name (and optionally guardName).
     *
     * @param string      $name
     * @param string|null $guardName
     *
     * @return PermissionContract
     * @throws PermissionDoesNotExist
     *
     */
    public static function findByName(
        string $name,
        $guardName = null
    ): PermissionContract {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);
        $permission = static::getPermissions(
            [
                'name' => $name,
                'guard_name' => $guardName,
            ]
        )->first();
        if (!$permission) {
            throw PermissionDoesNotExist::create($name, $guardName);
        }

        return $permission;
    }

    /**
     * Get the current cached permissions.
     *
     * @param array $params
     *
     * @return Collection
     */
    protected static function getPermissions(array $params = []): Collection
    {
        return app(PermissionRegistrar::class)->getPermissions($params);
    }

    /**
     * Find a permission by its id (and optionally guardName).
     *
     * @param int         $id
     * @param string|null $guardName
     *
     * @return PermissionContract
     * @throws PermissionDoesNotExist
     *
     */
    public static function findById(
        int $id,
        $guardName = null
    ): PermissionContract {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);
        $permission = static::getPermissions(
            [
                'id' => $id,
                'guard_name' => $guardName,
            ]
        )->first();

        if (!$permission) {
            throw PermissionDoesNotExist::withId($id, $guardName);
        }

        return $permission;
    }

    /**
     * Find or create permission by its name (and optionally guardName).
     *
     * @param string      $name
     * @param string|null $guardName
     *
     * @return PermissionContract
     */
    public static function findOrCreate(
        string $name,
        $guardName = null
    ): PermissionContract {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);
        $permission = static::getPermissions(
            [
                'name' => $name,
                'guard_name' => $guardName,
            ]
        )->first();

        if (!$permission) {
            return self::create(
                [
                    'name' => $name,
                    'guard_name' => $guardName,
                ]
            );
        }

        return $permission;
    }

    public static function create(array $attributes = [])
    {
        $attributes['guard_name'] =
            $attributes['guard_name'] ?? Guard::getDefaultName(static::class);
        $attributes['module_name'] = $attributes['module_name'] ?? 'General';

        $permission = static::getPermissions(
            [
                'name' => $attributes['name'],
                'guard_name' => $attributes['guard_name'],
            ]
        )->first();

        if ($permission) {
            throw PermissionAlreadyExists::create(
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
     * A permission can be applied to roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.role'),
            config('permission.table_names.role_has_permissions'),
            'permission_id',
            'role_id'
        );
    }

    /**
     * A permission belongs to some users of the model associated with its guard.
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(
            getModelForGuard($this->attributes['guard_name']),
            'model',
            config('permission.table_names.model_has_permissions'),
            'permission_id',
            config('permission.column_names.model_morph_key')
        );
    }
}
