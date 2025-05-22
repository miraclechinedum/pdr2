<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceiptsTable extends Migration
{
    public function up()
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('reference_number')->unique();
            $table->foreignId('customer_id')->constrained('users');
            $table->foreignId('seller_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('receipts');
    }
}
