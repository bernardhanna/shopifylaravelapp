<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-07-07 14:16:14
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-07-07 14:16:56
 */


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFulfillmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fulfillments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('shopify_fulfillment_id')->unique();
            $table->foreignId('order_id')->constrained();
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();
            $table->string('carrier_name')->nullable();
            $table->string('carrier_code')->nullable();
            $table->boolean('dispatched')->default(false);
            $table->timestamp('dispatched_at')->nullable();
            $table->string('despatch_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fulfillments');
    }
}