<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceiptProductsTable extends Migration
{
    public function up()
    {
        Schema::create('receipt_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('receipt_products');
    }
}
