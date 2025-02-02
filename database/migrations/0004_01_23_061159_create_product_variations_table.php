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
        Schema::create('variation_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('product_id')->index()->constrained('products')->cascadeOnDelete();
            $table->string('type');
        });
        Schema::create('variation_type_options', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('variation_type_id')->index()->constrained('variation_types')->cascadeOnDelete();
        });
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->index()->constrained('products')->cascadeOnDelete();
            $table->json('variation_type_option_ids');
            $table->integer('quantity')->nullable();
            $table->decimal('price', 20, 4)->nullable();
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variation_types');
        Schema::dropIfExists('variation_types_options');
        Schema::dropIfExists('product_variations');
    }
};
