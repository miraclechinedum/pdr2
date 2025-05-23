<?php

namespace App\Http\Controllers;

use App\Models\Pricing;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class PricingController extends Controller
{
    public function index()
    {
        return view('config.pricing');
    }

    public function data()
    {
        return DataTables::of(
            Pricing::with('creator')
                ->select(['id', 'service', 'amount', 'user_id', 'created_at', 'is_active'])
        )
            ->addColumn('created_by', fn($p) => $p->creator->name)
            ->addColumn('created_on', fn($p) => $p->created_at->format('jS M, Y'))
            // ->addColumn('active', fn($p) => $p->is_active ? 'Active' : 'Inactive')
            ->addColumn('actions', function (Pricing $p) {
                return '
          <label class="switch">
            <input type="checkbox" class="toggle-status" data-id="' . $p->id . '" '
                    . ($p->is_active ? 'checked' : '') . '>
            <span class="slider round"></span>
          </label>
          <button class="btn btn-sm btn-primary edit-btn p-2" data-id="' . $p->id . '" …>Edit</button>
        ';
            })
            ->rawColumns(['actions'])
            ->toJson();
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'service' => 'required|string|max:255|unique:pricings,service',
            'amount'  => 'required|numeric|min:0',
        ]);

        Pricing::create([
            'uuid'    => strtoupper(Str::random(4)),
            'service' => $data['service'],
            'amount'  => $data['amount'],
            'slug'    => Str::slug($data['service']),
            'user_id' => Auth::id(),
        ]);

        return response()->json(['success' => true], 201);
    }

    public function update(Request $request, Pricing $pricing)
    {
        $data = $request->validate([
            'service' => 'required|string|max:255|unique:pricings,service,' . $pricing->id,
            'amount'  => 'required|numeric|min:0',
        ]);

        $pricing->update([
            'service' => $data['service'],
            'amount'  => $data['amount'],
            'slug'    => Str::slug($data['service']),
            // keep original creator
        ]);

        return response()->json(['success' => true]);
    }

    public function toggle(Pricing $pricing)
    {
        $pricing->is_active = ! $pricing->is_active;
        $pricing->save();

        return response()->json([
            'success'   => true,
            'is_active' => $pricing->is_active,
        ]);
    }
}
