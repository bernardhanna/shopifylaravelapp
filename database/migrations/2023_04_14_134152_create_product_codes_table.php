<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-04-20 14:41:52
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-05-10 17:20:33
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
        Schema::create('product_codes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('basket_id')->index(); // add an index to basket_id
            $table->integer('basket_item_id');
            $table->string('code');
            $table->integer('quantity');
            $table->string('stockist');
            if (Schema::hasTable('orders')) {
                $table->foreign('basket_id')->references('basket_id')->on('orders');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_codes');
    }
};
