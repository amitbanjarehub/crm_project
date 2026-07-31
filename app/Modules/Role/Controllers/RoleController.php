<?php

namespace App\Modules\Role\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Role\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount([
            'users',
            'permissions',
        ])
            ->orderBy('name')
            ->get();

        return view('role::index', [
            'roles' => $roles,
            'pageTitle' => 'Role Management',
        ]);
    }

    public function create()
    {
        return view('role::create', [
            'pageTitle' => 'Create Role',
        ]);
    }

    // public function store(Request $request)
    // {
    //     /*
    //      * Extra spaces remove:
    //      * "Project    Manager" → "Project Manager"
    //      */
    //     $request->merge([
    //         'name' => preg_replace(
    //             '/\s+/',
    //             ' ',
    //             trim((string) $request->input('name'))
    //         ),
    //     ]);

    //     $validated = $request->validate([
    //         'name' => [
    //             'required',
    //             'string',
    //             'min:2',
    //             'max:100',
    //             Rule::unique('roles', 'name'),
    //         ],
    //     ]);

    //     /*
    //      * UI se accidental full-access role create hone se roko.
    //      * Admin aur Super Admin roles database se controlled rahenge.
    //      */
    //     $reservedAdminNames = [
    //         'admin',
    //         'administrator',
    //         'super admin',
    //         'super-admin',
    //     ];

    //     $normalizedName = strtolower(
    //         trim($validated['name'])
    //     );

    //     if (in_array(
    //         $normalizedName,
    //         $reservedAdminNames,
    //         true
    //     )) {
    //         return back()
    //             ->withErrors([
    //                 'name' => 'This is a reserved administrative role name.',
    //             ])
    //             ->withInput();
    //     }

    //     $role = Role::create([
    //         'name' => $validated['name'],
    //     ]);

    //     /*
    //      * Role create hone ke baad directly permission page
    //      * khulega, jahan Admin permissions select karega.
    //      */
    //     return redirect()
    //         ->route(
    //             'role.permissions.edit',
    //             $role->id
    //         )
    //         ->with(
    //             'success',
    //             'Role created successfully. Now assign its permissions.'
    //         );
    // }

    public function store(Request $request)
    {
        /*
         * Extra spaces remove:
         * "Project    Manager" becomes "Project Manager"
         */
        $request->merge([
            'name' => preg_replace(
                '/\s+/',
                ' ',
                trim((string) $request->input('name'))
            ),
        ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('roles', 'name'),
            ],
        ]);

        /*
         * Admin-type role names ko UI se create hone se block karo.
         */
        $reservedAdminNames = [
            'admin',
            'administrator',
            'super admin',
            'super-admin',
        ];

        $normalizedName = strtolower(
            trim($validated['name'])
        );

        if (
            in_array(
                $normalizedName,
                $reservedAdminNames,
                true
            )
        ) {
            return back()
                ->withErrors([
                    'name' => 'This is a reserved administrative role name.',
                ])
                ->withInput();
        }

        /*
         * Role name se slug generate hoga:
         *
         * Developer       => developer
         * Project Manager => project-manager
         * Sales Executive => sales-executive
         */
        $baseSlug = Str::slug($validated['name']);

        /*
         * Agar kisi reason se invalid characters ke baad
         * empty slug bane to fallback use hoga.
         */
        if ($baseSlug === '') {
            $baseSlug = 'role';
        }

        $slug = $baseSlug;
        $counter = 2;

        /*
         * Slug unique rakho.
         *
         * developer
         * developer-2
         * developer-3
         */
        while (
            Role::where('slug', $slug)->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $slug,
        ]);

        /*
         * Role create hone ke baad permission assignment page khulega.
         */
        return redirect()
            ->route(
                'role.permissions.edit',
                $role->id
            )
            ->with(
                'success',
                'Role created successfully. Now assign its permissions.'
            );
    }
}