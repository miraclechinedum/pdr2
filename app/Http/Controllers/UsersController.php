<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\State;
use App\Models\Lga;
use App\Models\Receipt;
use App\Models\Business;
use App\Models\BusinessBranch;
use App\Models\BusinessStaffs;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use App\Mail\NewUserCredentials;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $auth = Auth::user();

        // Base data
        $states   = State::all();
        $allRoles = Role::whereNotIn('name', ['Admin', 'Customer'])->get();
        $allPerms = Permission::all();
        $allBiz   = Business::all();

        if ($auth->hasRole('Business Owner')) {
            // Only Business Staff role
            $roles      = $allRoles->where('name', 'Business Staff');
            // Only product‐related permissions
            $perms      = $allPerms->whereIn('slug', [
                'create-product',
                'report-product',
                'resolve-product-report',
            ]);
            // Only businesses owned by this user
            $businesses = Business::where('owner_id', $auth->id)->get();
        } else {
            $roles      = $allRoles;
            $perms      = $allPerms;
            $businesses = $allBiz;
        }

        // JS map of role → permissions
        $rolePermissions = $roles->mapWithKeys(fn($r) => [
            $r->name => $r->permissions->pluck('name')->toArray(),
        ]);

        // Group for checkboxes
        $grouped = $perms->groupBy('category');

        return view('users.create', compact(
            'states',
            'roles',
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
            'nin'         => 'required|string|unique:users,nin',
            'phone_number' => 'required|string|unique:users,phone_number',
            'address'     => 'required|string',
            'state_id'    => 'required|exists:states,id',
            'lga_id'      => 'required|exists:lgas,id',
            'role'        => 'required|exists:roles,name',
            'permissions' => 'nullable|array',
            'business_id' => 'nullable|exists:businesses,id',
            'branch_id'   => 'nullable|exists:business_branches,id',
        ]);

        $role  = Role::where('name', $data['role'])->first();
        $plain = Str::random(12);

        $user = User::create([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'nin'          => $data['nin'],
            'phone_number' => $data['phone_number'],
            'address'      => $data['address'],
            'state_id'     => $data['state_id'],
            'lga_id'       => $data['lga_id'],
            'role_id'      => $role->id,
            'password'     => bcrypt($plain),
        ]);

        $user->assignRole($role->name);
        Mail::to($user->email)->send(new NewUserCredentials($user, $plain));

        if (!empty($data['permissions'])) {
            $user->givePermissionTo($data['permissions']);
        }

        if ($role->name === 'Business Staff') {
            DB::table('business_staffs')->insert([
                'business_id' => $data['business_id'],
                'user_id'     => $user->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function data(Request $request)
    {
        $authUser = auth()->user();
        $roleName = $authUser->getRoleNames()->first();

        $query = User::with('roles');

        if (in_array($roleName, ['Admin', 'Police'])) {
            $query->latest();
        } elseif ($roleName === 'Business Owner') {
            $businessId = Business::where('owner_id', $authUser->id)->value('id');

            if ($businessId) {
                $staffUserIds = BusinessStaffs::where('business_id', $businessId)
                    ->pluck('user_id')->unique();

                $ownerCustomerIds = Receipt::where('seller_id', $authUser->id)
                    ->pluck('customer_id')->unique();

                $staffCustomerIds = $staffUserIds->isNotEmpty()
                    ? Receipt::whereIn('seller_id', $staffUserIds)
                    ->pluck('customer_id')->unique()
                    : collect();

                $userIds = $staffUserIds
                    ->merge($ownerCustomerIds)
                    ->merge($staffCustomerIds)
                    ->unique();

                $query->when(
                    $userIds->isNotEmpty(),
                    fn($q) => $q->whereIn('id', $userIds),
                    fn($q) => $q->whereNull('id')
                );
            } else {
                $query->whereNull('id');
            }
        } elseif ($roleName === 'Business Staff') {
            $custIds = Receipt::where('seller_id', $authUser->id)
                ->pluck('customer_id')->unique();
            $query->whereIn('id', $custIds);
        } else {
            $query->whereNull('id');
        }

        return DataTables::of($query)
            ->addColumn('role', fn(User $u) => $u->getRoleNames()->first() ?? '-')
            ->addColumn('date_created', fn(User $u) => Carbon::parse($u->created_at)->format('jS M, Y g:ia'))
            ->addColumn('actions', function (User $u) use ($authUser) {
                $viewerRole = $authUser->getRoleNames()->first();
                $userRole   = $u->getRoleNames()->first();
                $editBtn    = (!($viewerRole === 'Admin' && $userRole === 'Admin'))
                    ? '<a href="' . route('users.edit', $u) . '" class="px-2 py-1 bg-yellow-500 text-white rounded">Edit</a>'
                    : '';
                $viewBtn = '<button data-uuid="' . $u->uuid . '" class="view-btn px-2 py-1 bg-blue-600 text-white rounded">View</button>';
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
        return response()->json(Lga::where('state_id', $stateId)->get(['id', 'name']));
    }

    public function getBranches($businessId)
    {
        return response()->json(
            BusinessBranch::where('business_id', $businessId)
                ->get(['id', 'branch_name'])
        );
    }

    public function edit(User $user)
    {
        $auth = Auth::user();

        $states   = State::all();
        $allRoles = Role::whereNotIn('name', ['Admin', 'Customer'])->get();
        $allPerms = Permission::all();
        $allBiz   = Business::all();

        if ($auth->hasRole('Business Owner')) {
            $roles      = $allRoles->where('name', 'Business Staff');
            $perms      = $allPerms->whereIn('slug', [
                'create-product',
                'report-product',
                'resolve-product-report'
            ]);
            $businesses = Business::where('owner_id', $auth->id)->get();
        } else {
            $roles      = $allRoles;
            $perms      = $allPerms;
            $businesses = $allBiz;
        }

        $rolePermissions = $roles->mapWithKeys(fn($r) => [
            $r->name => $r->permissions->pluck('name')->toArray()
        ]);

        $grouped = $perms->groupBy('category');

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

        $user->update([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'nin'          => $data['nin'],
            'phone_number' => $data['phone_number'],
            'address'      => $data['address'],
            'state_id'     => $data['state_id'],
            'lga_id'       => $data['lga_id'],
        ]);

        $user->syncRoles($data['role']);
        $user->syncPermissions($data['permissions'] ?? []);

        if ($data['role'] === 'Business Staff') {
            BusinessStaffs::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'business_id' => $data['business_id'],
                    'branch_id'  => $data['branch_id'],
                ]
            );
        } else {
            BusinessStaffs::where('user_id', $user->id)->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function findByNin($nin)
    {
        $u = User::where('nin', $nin)
            ->select('id', 'name', 'email', 'phone_number', 'address', 'state_id', 'lga_id')
            ->first();

        return response()->json($u ? $u : []);
    }
}
