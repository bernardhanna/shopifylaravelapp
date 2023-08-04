<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-04-20 14:41:23
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-07-07 15:15:29
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('basket_id')->index(); // add an index to basket_id
            $table->string('order_reference');
            $table->string('customer_title')->nullable();
            $table->string('customer_forename')->nullable();
            $table->string('customer_surname')->nullable();
            $table->string('customer_house_name_number')->nullable();
            $table->string('customer_line_1')->nullable();
            $table->string('customer_line_2')->nullable();
            $table->string('customer_line_3')->nullable();
            $table->string('customer_county')->nullable();
            $table->string('customer_postcode')->nullable();
            $table->string('customer_telephone')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('order_line')->nullable();
            $table->boolean('sent_to_shopify')->default(false);
            $table->boolean('fulfilled')->default(false);
            $table->bigInteger('shopify_order_id')->nullable();
            $table->string('shopify_order_number')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->boolean('rejected')->default(false);
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
        Schema::dropIfExists('orders');
    }
};
