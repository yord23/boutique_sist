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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            
            // Dinero con el que inicias (el cambio)
            $table->decimal('opening_balance', 10, 2)->default(0);
            
            // Ventas registradas por el sistema
            $table->decimal('cash_sales', 10, 2)->default(0);
            $table->decimal('card_sales', 10, 2)->default(0);
            
            // Datos administrativos (Utilidad)
            $table->decimal('total_costs', 10, 2)->default(0); // Inversión en mercancía
            $table->decimal('net_profit', 10, 2)->default(0);  // Ganancia real
            
            // Arqueo de caja
            $table->decimal('expected_cash', 10, 2)->default(0); // Lo que debería haber (Fondo + Ventas Efectivo)
            $table->decimal('actual_cash', 10, 2)->nullable();   // Lo que el cajero contó físicamente
            $table->decimal('difference', 10, 2)->default(0);    // Faltante o sobrante
            
            $table->enum('status', ['open', 'closed'])->default('open');
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
