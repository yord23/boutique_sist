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
        //
        // 1. Costo en las variantes (cuanto me cuesta hoy)
        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('cost_price', 10, 2)->after('price')->default(0);
        });

        // 2. Costo en el detalle de la venta (a cuanto lo compré cuando lo vendí)
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('cost_price', 10, 2)->after('price')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
