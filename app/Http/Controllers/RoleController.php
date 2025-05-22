<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function getPermissions($roleName)
    {
        $role = Role::where('name', $roleName)->firstOrFail();
        $perms = $role->permissions->pluck('name');
        return response()->json(['permissions' => $perms]);
    }
}
