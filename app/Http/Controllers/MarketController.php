<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Market;

class MarketController extends Controller
{
    public function getUserMarkets(Request $request)
    {
        $userId = $request->query('user_id');

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                return response()->json([], 404);
            }
        } else {
            $user = $request->user();
        }

        if (!empty($user->market_ownership)) {
            $ids = array_filter(array_map('trim', explode(',', $user->market_ownership)));
            if (count($ids) === 0) {
                return response()->json([]);
            }

            $markets = Market::whereIn('id', $ids)
                ->select('id', 'name')
                ->get();

            return response()->json($markets);
        }

        $markets = $user->markets()
            ->select('id', 'name')
            ->get();

        return response()->json($markets);
    }

    public function applyFilters(Request $request)
    {
        $request->validate([
            'markets' => 'required|string',
            'fromDate' => 'required|date',
            'toDate' => 'required|date|after_or_equal:fromDate'
        ]);

        return response()->json([
            'filters' => $request->all(),
            'message' => 'Filters applied successfully'
        ]);
    }
}