<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-11 11:18:36
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-06-01 17:34:24
 */


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
        Schema::create('variants', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained();
            $table->id();
            $table->string('title');
            $table->float('price');
            $table->boolean('available')->default(false);
            $table->integer('inventory')->nullable();
            $table->string('sku')->nullable();
            $table->integer('position')->default(0);
            $table->string('inventory_item_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variants');
    }
};
