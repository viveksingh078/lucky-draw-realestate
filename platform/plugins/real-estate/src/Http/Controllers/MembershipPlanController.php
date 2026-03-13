<?php

namespace Botble\RealEstate\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MembershipPlanController extends BaseController
{
    public function index()
    {
        page_title()->setTitle('Membership Plans');

        $plans = DB::table('membership_plans')
            ->orderBy('sort_order')
            ->get();

        return view('plugins/real-estate::membership-plans.index', compact('plans'));
    }

    public function create()
    {
        page_title()->setTitle('Create Membership Plan');

        return view('plugins/real-estate::membership-plans.create');
    }

    public function store(Request $request, BaseHttpResponse $response)
    {
        $request->validate([
            'name' => 'required|max:255',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'description' => 'nullable|max:500',
        ]);

        $features = [];
        if ($request->has('features')) {
            $features = array_filter($request->input('features'));
        }

        DB::table('membership_plans')->insert([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'duration_days' => $request->input('duration_days'),
            'features' => json_encode($features),
            'is_active' => $request->input('is_active', 1),
            'sort_order' => $request->input('sort_order', 0),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $response
            ->setPreviousUrl(route('membership-plans.index'))
            ->setMessage('Membership plan created successfully!');
    }

    public function edit($id)
    {
        page_title()->setTitle('Edit Membership Plan');

        $plan = DB::table('membership_plans')->where('id', $id)->first();

        if (!$plan) {
            abort(404);
        }

        return view('plugins/real-estate::membership-plans.edit', compact('plan'));
    }

    public function update($id, Request $request, BaseHttpResponse $response)
    {
        $request->validate([
            'name' => 'required|max:255',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'description' => 'nullable|max:500',
        ]);

        $features = [];
        if ($request->has('features')) {
            $features = array_filter($request->input('features'));
        }

        DB::table('membership_plans')->where('id', $id)->update([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'duration_days' => $request->input('duration_days'),
            'features' => json_encode($features),
            'is_active' => $request->input('is_active', 1),
            'sort_order' => $request->input('sort_order', 0),
            'updated_at' => now(),
        ]);

        return $response
            ->setPreviousUrl(route('membership-plans.index'))
            ->setMessage('Membership plan updated successfully!');
    }

    public function destroy($id, BaseHttpResponse $response)
    {
        // Check if any accounts are using this plan
        $accountsCount = DB::table('re_accounts')
            ->where('membership_plan_id', $id)
            ->count();

        if ($accountsCount > 0) {
            return $response
                ->setError()
                ->setMessage('Cannot delete this plan. ' . $accountsCount . ' account(s) are using it.');
        }

        DB::table('membership_plans')->where('id', $id)->delete();

        return $response->setMessage('Membership plan deleted successfully!');
    }
}
