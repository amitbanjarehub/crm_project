<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Modules\Role\Models\Role;


class UserController extends Controller
{
    // public function index(Request $request)
    // {
    //     $allowedPerPage = [10, 25, 50, 100];

    //     $perPage = (int) $request->query('per_page', 10);

    //     if (!in_array($perPage, $allowedPerPage)) {
    //         $perPage = 10;
    //     }

    //     $users = User::latest()
    //         ->paginate($perPage)
    //         ->appends($request->query());

    //     return view('user::index', compact('users', 'perPage'))->with([
    //         'pageTitle' => 'User Management'
    //     ]);
    // }


    public function index(Request $request)
    {
        $allowedPerPage = [10, 25, 50, 100];

        $perPage = (int) $request->query('per_page', 10);

        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        $search = trim($request->query('search', ''));

        $query = User::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('email', 'LIKE', '%' . $search . '%');

                if (is_numeric($search)) {
                    $q->orWhere('id', $search);
                }
            });
        }

        $users = $query->latest()
            ->paginate($perPage)
            ->appends($request->query());

        return view('user::index', compact('users', 'perPage', 'search'))->with([
            'pageTitle' => 'User Management'
        ]);
    }

    // public function edit(User $user)
    // {
    //     return view('user::edit', compact('user'))->with([
    //         'pageTitle' => 'Edit User'
    //     ]);
    // }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();

        return view('user::edit', compact('user', 'roles'))->with([
            'pageTitle' => 'Edit User'
        ]);
    }

    // public function update(Request $request, User $user)
    // {
    //     $request->validate([
    //         'name' => ['required', 'string', 'max:255'],

    //         'email' => [
    //             'required',
    //             'email',
    //             'max:255',
    //             Rule::unique('users', 'email')->ignore($user->id),
    //         ],

    //         'password' => ['nullable', 'string', 'min:8'],
    //     ]);

    //     $user->name = $request->name;
    //     $user->email = $request->email;

    //     if ($request->filled('password')) {
    //         $user->password = Hash::make($request->password);
    //     }

    //     $user->save();

    //     return redirect()
    //         ->route('user.index')
    //         ->with('success', 'User updated successfully.');
    // }


    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
            ],

            'role_id' => [
                'required',
                'exists:roles,id',
            ],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role_id = $request->role_id;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()
            ->route('user.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() == $user->id) {
            return redirect()
                ->route('user.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()
            ->route('user.index')
            ->with('success', 'User deleted successfully.');
    }

    public function updateStatus(Request $request, User $user)
    {
        $validated = $request->validate([
            'is_active' => [
                'required',
                'boolean',
            ],
        ]);

        $newStatus = (bool) $validated['is_active'];

        /*
         * Logged-in admin apna khud ka account deactivate nahi kar sakta.
         */
        if (auth()->id() === $user->id && $newStatus === false) {
            return redirect()
                ->route('user.index')
                ->with('error', 'You cannot deactivate your own account.');
        }

        $user->is_active = $newStatus;

        /*
         * Deactivate hone par remember-me token hata denge.
         */
        if ($newStatus === false) {
            $user->remember_token = null;
        }

        $user->save();

        $message = $newStatus
            ? 'User activated successfully.'
            : 'User deactivated successfully.';

        return redirect()
            ->route('user.index')
            ->with('success', $message);
    }

    // public function create()
    // {
    //     return view('user::create')->with([
    //         'pageTitle' => 'Add User'
    //     ]);
    // }

    public function create()
    {
        $roles = Role::orderBy('name')->get();

        return view('user::create', compact('roles'))->with([
            'pageTitle' => 'Add User'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],

            'password' => ['required', 'string', 'min:8', 'confirmed'],

            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role_id = $request->role_id;
        $user->is_active = true;

        $user->save();

        return redirect()
            ->route('user.index')
            ->with('success', 'User added successfully.');
    }
}