<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('coins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users');
            $table->string('symbol')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->decimal('initial_price', 20, 8)->default(1);
            $table->decimal('current_price', 20, 8)->default(1);
            $table->decimal('total_supply', 30, 8);
            $table->decimal('circulating_supply', 30, 8)->default(0);
            $table->decimal('market_cap', 30, 2)->default(0);
            $table->decimal('volume_24h', 30, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('coin_id')->constrained();
            $table->decimal('balance', 30, 8)->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'coin_id']);
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('coin_id')->constrained();
            $table->enum('type', ['buy', 'sell']);
            $table->decimal('entry_price', 20, 8);
            $table->decimal('current_price', 20, 8);
            $table->decimal('lot_size', 20, 8);
            $table->integer('leverage')->default(1);
            $table->decimal('margin', 20, 8);
            $table->decimal('pnl', 20, 8)->default(0);
            $table->boolean('is_open')->default(true);
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('coin_id')->constrained();
            $table->enum('type', ['buy', 'sell', 'deposit', 'withdraw']);
            $table->decimal('amount', 30, 8);
            $table->decimal('price', 20, 8);
            $table->decimal('total', 30, 8);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('coins');
    }
};
