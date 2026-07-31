<?php

namespace App\Modules\Role\Models;

use App\Modules\Permission\Models\Permission;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\TimeTracking\Models\TimeEntry;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'name',
        'slug',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(
            TimeEntry::class,
            'role_id'
        );
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'permission_role',
            'role_id',
            'permission_id'
        )->withTimestamps();
    }

    public function isAdminRole(): bool
    {
        $roleName = strtolower(trim($this->name));

        return in_array($roleName, [
            'admin',
            'administrator',
            'super admin',
            'super-admin',
        ], true);
    }
}