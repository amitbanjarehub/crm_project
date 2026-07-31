<?php

namespace App\Modules\Permission\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::query()
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy(function (Permission $permission) {
                return $permission->group ?: 'Other';
            });

        return view('permission::index', compact('permissions'))->with([
            'pageTitle' => 'Permission Management',
        ]);
    }
}