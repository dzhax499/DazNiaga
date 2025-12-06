<?php

namespace App\Http\Controllers;

use App\Models\Coin;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CoinController extends Controller
{
    public function index()
    {
        $coins = Coin::with('creator')
            ->where('is_active', true)
            ->orderBy('market_cap', 'desc')
            ->get();

        return response()->json($coins);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'symbol' => 'required|string|max:10|unique:coins',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|max:2048',
            'initial_price' => 'required|numeric|min:0.00000001',
            'total_supply' => 'required|numeric|min:1',
        ]);

        $validated['creator_id'] = auth()->id();
        $validated['current_price'] = $validated['initial_price'];
        $validated['circulating_supply'] = $validated['total_supply'];

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('coins', 'public');
        }

        $coin = Coin::create($validated);
        $coin->updateMarketCap();

        // Give creator the initial supply
        Wallet::create([
            'user_id' => auth()->id(),
            'coin_id' => $coin->id,
            'balance' => $validated['total_supply']
        ]);

        return response()->json([
            'message' => 'Coin created successfully',
            'coin' => $coin
        ], 201);
    }

    public function show(Coin $coin)
    {
        $coin->load('creator');
        return response()->json($coin);
    }

    public function updatePrice(Coin $coin)
    {
        // Simulate price movement
        $change = (mt_rand(-100, 100) / 10000);
        $coin->current_price = $coin->current_price * (1 + $change);
        $coin->updateMarketCap();

        return response()->json($coin);
    }

    public function myCoins()
    {
        $coins = Coin::where('creator_id', auth()->id())->get();
        return response()->json($coins);
    }
}
