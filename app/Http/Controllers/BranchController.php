<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessBranch;
use App\Models\State;
use App\Models\Lga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class BranchController extends Controller
{
    public function index()
    {
        return view('branches.index');
    }

    public function data()
    {
        return DataTables::of(BusinessBranch::with(['business', 'state', 'lga']))
            ->addColumn('business', fn($b) => $b->business->business_name)
            ->addColumn('state',    fn($b) => $b->state->name)
            ->addColumn('lga',      fn($b) => $b->lga->name)
            ->addColumn(
                'actions',
                fn($b) =>
                '<a href="' . route('branches.edit', $b->uuid) . '" class="px-2 py-1 bg-yellow-500 text-white rounded">Edit</a>'
            )
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function create()
    {
        $businesses = Business::pluck('business_name', 'id');
        $states     = State::pluck('name', 'id');
        return view('branches.create', compact('businesses', 'states'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'branch_name' => 'required|string|max:255',
            'address'     => 'required|string|max:255',
            'state_id'    => 'required|exists:states,id',
            'lga_id'      => 'required|exists:lgas,id',
        ]);

        BusinessBranch::create([
            'business_id' => $data['business_id'],
            'branch_name' => $data['branch_name'],
            'address'     => $data['address'],
            'state_id'    => $data['state_id'],
            'lga_id'      => $data['lga_id'],
            'status'      => 'active',
            'user_id'     => Auth::id(),
        ]);

        return redirect()->route('branches.index')
            ->with('success', 'Branch created.');
    }

    public function edit(string $uuid)
    {
        $branch     = BusinessBranch::where('uuid', $uuid)->firstOrFail();
        $businesses = Business::pluck('business_name', 'id');
        $states     = State::pluck('name', 'id');
        return view('branches.edit', compact('branch', 'businesses', 'states'));
    }

    public function update(Request $request, string $uuid)
    {
        $branch = BusinessBranch::where('uuid', $uuid)->firstOrFail();

        $data = $request->validate([
            'business_id'  => 'required|exists:businesses,id',
            'branch_name'  => 'required|string|max:255',       // ← new
            'address'      => 'required|string|max:255',
            'state_id'     => 'required|exists:states,id',
            'lga_id'       => 'required|exists:lgas,id',
        ]);

        $branch->update([
            'business_id'  => $data['business_id'],
            'branch_name'  => $data['branch_name'],           // ← new
            'address'      => $data['address'],
            'state_id'     => $data['state_id'],
            'lga_id'       => $data['lga_id'],
        ]);

        return redirect()->route('branches.index')
            ->with('success', 'Branch updated.');
    }
}
