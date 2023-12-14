<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tron_wallets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('title')->nullable();
            $table->text('password');
            $table->text('mnemonic');
            $table->text('seed');
            $table->timestamp('sync_at')->nullable();
            $table->decimal('balance', 20, 6)->nullable();
            $table->json('trc20')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tron_wallets');
    }
};
