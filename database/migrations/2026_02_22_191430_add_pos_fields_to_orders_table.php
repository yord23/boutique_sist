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
        Schema::table('orders', function (Blueprint $table) {
        // 1. Datos del comprobante (después del ID)
            $table->enum('document_type', ['ticket', 'invoice', 'receipt'])->default('ticket')->after('id');
            $table->string('order_number')->unique()->after('document_type');
            
            // 2. Impuestos (después de tu columna existente 'total')
            $table->decimal('tax_amount', 12, 2)->default(0)->after('total');
            
            // 3. Flujo de caja (dinero que entra y sale)
            $table->decimal('received_amount', 12, 2)->default(0)->after('tax_amount');
            $table->decimal('change_amount', 12, 2)->default(0)->after('received_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->dropColumn([
                'document_type', 
                'order_number', 
                'tax_amount', 
                'received_amount', 
                'change_amount'
            ]);
        });
    }
};
