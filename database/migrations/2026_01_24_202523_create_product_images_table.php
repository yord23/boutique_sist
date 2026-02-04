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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            // Relación con el producto (Si se borra el producto, se borran sus fotos)
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Relación opcional con color: Para que al elegir "Verde" en Vue, se filtren estas fotos
            $table->foreignId('color_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('file_path'); // Ruta: products/nombre-archivo.jpg
            $table->boolean('is_primary')->default(false); // La foto que sale en el listado general
            $table->integer('position')->default(0); // Para que tú decidas el orden (1, 2, 3...)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
