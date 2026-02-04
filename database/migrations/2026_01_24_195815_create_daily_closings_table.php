<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // Quien cierra caja
            $table->date('date');
            $table->decimal('opening_balance', 10, 2); // Con cuánto inició
            $table->decimal('cash_sales', 10, 2);
            $table->decimal('card_sales', 10, 2);
            $table->decimal('final_balance', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_closings');
    }
};
