<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coin extends Model
{
    protected $fillable = [
        'creator_id',
        'symbol',
        'name',
        'description',
        'logo',
        'initial_price',
        'current_price',
        'total_supply',
        'circulating_supply',
        'market_cap',
        'volume_24h',
        'is_active'
    ];

    protected $casts = [
        'initial_price' => 'decimal:8',
        'current_price' => 'decimal:8',
        'total_supply' => 'decimal:8',
        'circulating_supply' => 'decimal:8',
        'market_cap' => 'decimal:2',
        'volume_24h' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    public function updateMarketCap()
    {
        $this->market_cap = $this->current_price * $this->circulating_supply;
        $this->save();
    }
}
