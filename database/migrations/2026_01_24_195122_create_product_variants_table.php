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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('size_id')->constrained();
            $table->foreignId('color_id')->constrained();
            $table->string('barcode')->unique()->nullable(); // Código único para etiquetas
            $table->integer('stock')->default(0);
            $table->decimal('price', 10, 2)->nullable(); // Solo si cambia respecto al base
            $table->timestamps();
            
            $table->softDeletes(); // Para no borrar ventas históricas si el empleado se va
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
