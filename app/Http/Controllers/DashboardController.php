<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\Business;
use App\Models\BusinessBranch;
use App\Models\BusinessStaffs;
use App\Models\ReportedProduct;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user->getRoleNames()->first();

        // 1) Total Receipts Generated
        if (in_array($role, ['Admin', 'Police'])) {
            $totalReceipts = Receipt::count();
        } elseif ($user->hasRole('Business Owner')) {
            // all receipts created by this owner or their staff
            $bizIds = Business::where('owner_id', $user->id)->pluck('id');
            $staffIds = BusinessStaffs::whereIn('business_id', $bizIds)
                ->pluck('user_id')
                ->push($user->id)
                ->unique();
            $totalReceipts = Receipt::whereIn('seller_id', $staffIds)->count();
        } elseif ($user->hasRole('Business Staff')) {
            $totalReceipts = Receipt::where('seller_id', $user->id)->count();
        } else {
            // everyone else sees receipts generated *for* them
            $totalReceipts = Receipt::where('customer_id', $user->id)->count();
        }

        // 2) Total Reported Products
        if (in_array($role, ['Admin', 'Police'])) {
            $totalReported = ReportedProduct::count();
        } elseif ($user->hasRole('Business Owner')) {
            // reported by this owner’s staff
            $bizIds = Business::where('owner_id', $user->id)->pluck('id');
            $staffIds = BusinessStaffs::whereIn('business_id', $bizIds)
                ->pluck('user_id');
            $totalReported = ReportedProduct::whereIn('user_id', $staffIds)->count();
        } else {
            // business staff and everyone else: their own reports
            $totalReported = ReportedProduct::where('user_id', $user->id)->count();
        }

        // 3) Total Resolved Products (assuming status=0 means resolved)
        if (in_array($role, ['Admin', 'Police'])) {
            $totalResolved = ReportedProduct::where('status', 0)->count();
        } elseif ($user->hasRole('Business Owner')) {
            $bizIds = Business::where('owner_id', $user->id)->pluck('id');
            $staffIds = BusinessStaffs::whereIn('business_id', $bizIds)
                ->pluck('user_id');
            $totalResolved = ReportedProduct::where('status', 0)
                ->whereIn('user_id', $staffIds)
                ->count();
        } else {
            $totalResolved = ReportedProduct::where('status', 0)
                ->where('user_id', $user->id)
                ->count();
        }

        // 4) Total Businesses (only admin/police/owner)
        $totalBusinesses = null;
        if (in_array($role, ['Admin', 'Police'])) {
            $totalBusinesses = Business::count();
        } elseif ($user->hasRole('Business Owner')) {
            $totalBusinesses = Business::where('owner_id', $user->id)->count();
        }

        // 5) Total Branches (only admin/police/owner)
        $totalBranches = null;
        if (in_array($role, ['Admin', 'Police'])) {
            $totalBranches = BusinessBranch::count();
        } elseif ($user->hasRole('Business Owner')) {
            $bizIds = Business::where('owner_id', $user->id)->pluck('id');
            $totalBranches = BusinessBranch::whereIn('business_id', $bizIds)->count();
        }

        return view('dashboard', compact(
            'totalReceipts',
            'totalReported',
            'totalResolved',
            'totalBusinesses',
            'totalBranches'
        ));
    }
}
