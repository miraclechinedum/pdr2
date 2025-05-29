<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SgdController extends Controller
{
    public function index()
    {
        return view('sgd.index');
    }

    public function data(Request $request)
    {
        // static sample data
        $records = collect([
            [
                'product_name'       => 'Widget A',
                'unique_identifier'  => 'WID-A-001',
                'importer_name'      => 'Acme Imports',
                'importer_contact'   => '08012345678',
                'date'               => '2025-05-28',
            ],
            [
                'product_name'       => 'Gadget B',
                'unique_identifier'  => 'GDT-B-002',
                'importer_name'      => 'Global Traders',
                'importer_contact'   => '08087654321',
                'date'               => '2025-05-27',
            ],
            // …add as many static rows as you like
        ]);

        return DataTables::of($records)
            ->make(true);
    }
}
