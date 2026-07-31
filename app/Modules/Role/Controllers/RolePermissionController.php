<?php

namespace App\Modules\Role\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Permission\Models\Permission;
use App\Modules\Role\Models\Role;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    public function edit(Role $role)
    {
        $permissions = Permission::query()
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy(function (Permission $permission) {
                return $permission->group ?: 'Other';
            });

        $isAdminRole = $role->isAdminRole();

        if ($isAdminRole) {
            $assignedPermissions = Permission::pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        } else {
            $assignedPermissions = $role->permissions()
                ->pluck('permissions.id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return view('role::permissions', compact(
            'role',
            'permissions',
            'assignedPermissions',
            'isAdminRole'
        ))->with([
            'pageTitle' => 'Role Permissions',
        ]);
    }

    public function update(Request $request, Role $role)
    {
        if ($role->isAdminRole()) {
            return redirect()
                ->route('role.permissions.edit', $role->id)
                ->with(
                    'error',
                    'Admin role always has full permissions and cannot be restricted.'
                );
        }

        $validated = $request->validate([
            'permissions' => [
                'nullable',
                'array',
            ],

            'permissions.*' => [
                'integer',
                'exists:permissions,id',
            ],
        ]);

        $role->permissions()->sync(
            $validated['permissions'] ?? []
        );

        return redirect()
            ->route('role.permissions.edit', $role->id)
            ->with(
                'success',
                'Permissions saved successfully for ' . $role->name . '.'
            );
    }
}