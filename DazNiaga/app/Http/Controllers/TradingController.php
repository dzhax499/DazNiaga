<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Coin;
use App\Models\Wallet;
use Illuminate\Http\Request;

class TradingController extends Controller
{
    public function openPosition(Request $request)
    {
        $validated = $request->validate([
            'coin_id' => 'required|exists:coins,id',
            'type' => 'required|in:buy,sell',
            'lot_size' => 'required|numeric|min:0.01',
            'leverage' => 'required|integer|min:1|max:100',
        ]);

        $coin = Coin::findOrFail($validated['coin_id']);
        $user = auth()->user();

        $margin = ($coin->current_price * $validated['lot_size']) / $validated['leverage'];

        // Check user balance (assuming USD wallet with coin_id = 1)
        $usdWallet = Wallet::firstOrCreate(
            ['user_id' => $user->id, 'coin_id' => 1],
            ['balance' => 10000] // Initial balance
        );

        if ($usdWallet->balance < $margin) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        // Deduct margin
        $usdWallet->balance -= $margin;
        $usdWallet->save();

        $position = Position::create([
            'user_id' => $user->id,
            'coin_id' => $validated['coin_id'],
            'type' => $validated['type'],
            'entry_price' => $coin->current_price,
            'current_price' => $coin->current_price,
            'lot_size' => $validated['lot_size'],
            'leverage' => $validated['leverage'],
            'margin' => $margin,
        ]);

        return response()->json([
            'message' => 'Position opened successfully',
            'position' => $position
        ], 201);
    }

    public function closePosition(Position $position)
    {
        if ($position->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$position->is_open) {
            return response()->json(['error' => 'Position already closed'], 400);
        }

        $coin = $position->coin;

        // Calculate final P&L
        if ($position->type === 'buy') {
            $pnl = ($coin->current_price - $position->entry_price) * $position->lot_size * $position->leverage;
        } else {
            $pnl = ($position->entry_price - $coin->current_price) * $position->lot_size * $position->leverage;
        }

        // Return margin + P&L to wallet
        $usdWallet = Wallet::where('user_id', $position->user_id)
            ->where('coin_id', 1)
            ->first();

        $usdWallet->balance += ($position->margin + $pnl);
        $usdWallet->save();

        $position->update([
            'is_open' => false,
            'current_price' => $coin->current_price,
            'pnl' => $pnl,
            'closed_at' => now()
        ]);

        return response()->json([
            'message' => 'Position closed successfully',
            'position' => $position,
            'pnl' => $pnl
        ]);
    }

    public function getPositions()
    {
        $positions = Position::with('coin')
            ->where('user_id', auth()->id())
            ->where('is_open', true)
            ->get();

        return response()->json($positions);
    }

    public function updatePositions()
    {
        $positions = Position::where('user_id', auth()->id())
            ->where('is_open', true)
            ->get();

        foreach ($positions as $position) {
            $coin = $position->coin;
            $position->current_price = $coin->current_price;

            if ($position->type === 'buy') {
                $position->pnl = ($coin->current_price - $position->entry_price) * $position->lot_size * $position->leverage;
            } else {
                $position->pnl = ($position->entry_price - $coin->current_price) * $position->lot_size * $position->leverage;
            }

            $position->save();
        }

        return response()->json($positions);
    }
}
