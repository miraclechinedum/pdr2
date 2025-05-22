<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportedProductsTable extends Migration
{
    public function up()
    {
        Schema::create('reported_products', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reported_products');
    }
}
