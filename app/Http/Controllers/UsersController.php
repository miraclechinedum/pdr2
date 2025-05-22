<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\State;
use App\Models\Lga;
use App\Models\Business;
use App\Models\BusinessBranch;
use App\Models\BusinessStaffs;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

use Illuminate\Support\Str;
use App\Mail\NewUserCredentials;
use Illuminate\Support\Facades\Mail;


class UsersController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $states       = State::all();
        $roles        = Role::whereNotIn('name', ['Admin', 'Customer'])->get();
        $permissions  = Permission::all();
        $businesses   = Business::all();

        // Build array: roleName => [permName, …]
        $rolePermissions = $roles
            ->mapWithKeys(fn($role) => [
                $role->name => $role->permissions->pluck('name')->toArray()
            ])->toArray();

        // Group permissions by category
        $grouped = Permission::orderBy('category')
            ->get()
            ->groupBy('category');

        return view('users.create', compact(
            'states',
            'roles',
            'permissions',
            'businesses',
            'rolePermissions',
            'grouped'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string',
            'email'       => 'required|email|unique:users',
            'nin'          => 'required|string|unique:users,nin',
            'phone_number' => 'required|string|unique:users,phone_number',
            'address'     => 'required|string',
            'state_id'    => 'required|exists:states,id',
            'lga_id'      => 'required|exists:lgas,id',
            'role'        => 'required|exists:roles,name',
            'permissions' => 'nullable|array',
            'business_id' => 'nullable|exists:businesses,id',
            'branch_id'   => 'nullable|exists:business_branches,id',
        ]);

        // Find the Role to get its ID
        $role = Role::where('name', $data['role'])->first();
        $plain = Str::random(12);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'nin'      => $data['nin'],
            'phone_number'    => $data['phone_number'],
            'address'  => $data['address'],
            'state_id' => $data['state_id'],
            'lga_id'   => $data['lga_id'],
            'role_id'  => $role->id,
            'password'     => bcrypt($plain),
        ]);

        // assign via spatie too, if you like
        $user->assignRole($role->name);
        Mail::to($user->email)
            ->send(new NewUserCredentials($user, $plain));

        if (!empty($data['permissions'])) {
            $user->givePermissionTo($data['permissions']);
        }

        if ($role->name === 'Business Staff') {
            DB::table('business_staffs')->insert([
                'business_id' => $data['business_id'],
                'user_id'    => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function data(Request $request)
    {
        $query = User::with('roles');

        return DataTables::of($query)
            ->addColumn('role', fn(User $user) =>
            $user->getRoleNames()->first() ?? '-')
            ->addColumn('actions', function (User $user) {
                $editBtn = '<a href="' . route('users.edit', $user) . '" '
                    . 'class="px-2 py-1 bg-yellow-500 text-white rounded">Edit</a>';
                $viewBtn = '<button data-uuid="' . $user->uuid . '" '
                    . 'class="view-btn px-2 py-1 bg-blue-600 text-white rounded">'
                    . 'View</button>';
                return $editBtn . ' ' . $viewBtn;
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function permissions(User $user)
    {
        return response()->json($user->getPermissionNames());
    }

    public function getLgas($stateId)
    {
        $lgas = Lga::where('state_id', $stateId)->get(['id', 'name']);
        return response()->json($lgas);
    }

    public function getBranches($businessId)
    {
        $branches = BusinessBranch::where('business_id', $businessId)
            ->get(['id', 'branch_name']);
        return response()->json($branches);
    }

    public function edit(User $user)
    {
        $states       = State::all();
        $roles        = Role::whereNotIn('name', ['Admin', 'Customer'])->get();
        $businesses   = Business::all();
        $grouped      = Permission::orderBy('category')->get()->groupBy('category');

        // role → default perms
        $rolePermissions = $roles
            ->mapWithKeys(fn($role) => [
                $role->name => $role->permissions->pluck('name')->toArray()
            ])->toArray();

        // Group permissions by category
        $grouped = Permission::orderBy('category')
            ->get()
            ->groupBy('category');

        return view('users.edit', compact(
            'user',
            'states',
            'roles',
            'businesses',
            'rolePermissions',
            'grouped'
        ));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'           => 'required|string',
            'email'          => "required|email|unique:users,email,{$user->id}",
            'nin'            => "required|string|unique:users,nin,{$user->id}",
            'phone_number'   => "required|string|unique:users,phone_number,{$user->id}",
            'address'        => 'required|string',
            'state_id'       => 'required|exists:states,id',
            'lga_id'         => 'required|exists:lgas,id',
            'role'           => 'required|exists:roles,name',
            'permissions'    => 'nullable|array',
            'business_id'    => 'nullable|exists:businesses,id',
            'branch_id'      => 'nullable|exists:business_branches,id',
        ]);

        // 1) Update the user’s core attributes
        $user->update([
            'name'           => $data['name'],
            'email'          => $data['email'],
            'nin'            => $data['nin'],
            'phone_number'   => $data['phone_number'],
            'address'        => $data['address'],
            'state_id'       => $data['state_id'],
            'lga_id'         => $data['lga_id'],
        ]);

        // 2) Sync their role
        $user->syncRoles($data['role']);

        // 3) Sync their permissions
        $user->syncPermissions($data['permissions'] ?? []);

        // 4) Handle business_staffs pivot
        if ($data['role'] === 'Business Staff') {
            // either update or create
            BusinessStaffs::updateOrCreate(
                ['user_id'     => $user->id],
                [
                    'business_id' => $data['business_id'],
                    'branch_id'   => $data['branch_id'],
                ]
            );
        } else {
            // if they used to be a staff, remove that record
            BusinessStaffs::where('user_id', $user->id)->delete();
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Return a user by NIN as JSON, or an empty object.
     */
    public function findByNin($nin)
    {
        $user = User::where('nin', $nin)
            ->select('id', 'name', 'email', 'phone_number', 'address', 'state_id', 'lga_id')
            ->first();

        if (! $user) {
            return response()->json([], 200);
        }

        return response()->json($user, 200);
    }
}
