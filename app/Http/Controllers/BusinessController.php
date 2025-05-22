<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\State;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class BusinessController extends Controller
{
    public function index()
    {
        return view('businesses.index');
    }

    public function data()
    {
        return DataTables::of(Business::with('owner'))
            ->addColumn('owner', fn($b) => $b->owner->name)
            ->addColumn(
                'actions',
                fn($b) =>
                '<a href="' . route('businesses.edit', $b->uuid) . '" class="px-2 py-1 bg-yellow-500 text-white rounded">Edit</a>'
            )
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function create()
    {
        $owners = User::role('Business Owner')->get();
        $states = State::all();
        return view('businesses.create', compact('owners', 'states'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'business_name' => 'required|string|max:255',
            'rc_number'     => 'nullable|string|max:255',
            'email'         => 'nullable|email|max:255',
            'phone'         => 'nullable|string|max:50',
            'owner_id'      => 'required|exists:users,id',
            'address'     => 'required|string|max:255',
            'state_id'      => 'required|exists:states,id',
            'lga_id'        => 'required|exists:lgas,id',
        ]);

        Business::create([
            'owner_id'      => $data['owner_id'],
            'rc_number'     => $data['rc_number'],
            'business_name' => $data['business_name'],
            'email'         => $data['email'],
            'phone'         => $data['phone'],
            'address'         => $data['address'],
            'state_id'      => $data['state_id'],
            'lga_id'        => $data['lga_id'],
        ]);

        return redirect()->route('businesses.index')
            ->with('success', 'Business created successfully.');
    }

    public function edit(string $uuid)
    {
        $business = Business::where('uuid', $uuid)->firstOrFail();
        $owners   = User::role('Business Owner')->get();     // in case you ever want to reassign
        $states   = State::all();

        return view('businesses.edit', compact(
            'business',
            'owners',
            'states'
        ));
    }

    public function update(Request $request, string $uuid)
    {
        $business = Business::where('uuid', $uuid)->firstOrFail();
        $data = $request->validate([
            'rc_number'     => 'nullable|string|max:255',
            'business_name' => 'required|string|max:255',
            'email'         => 'nullable|email|max:255',
            'phone'         => 'nullable|string|max:50',
        ]);
        $business->update($data);

        return redirect()->route('businesses.index')
            ->with('success', 'Business updated.');
    }
}
