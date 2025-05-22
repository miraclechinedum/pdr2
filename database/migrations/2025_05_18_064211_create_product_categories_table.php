<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();

            // 6-digit unique code
            $table->string('uuid', 6)
                ->unique()
                ->comment('6-digit auto-generated identifier');

            $table->string('name')
                ->comment('Human-readable category name, e.g. "Phones"');

            $table->string('label')
                ->unique()
                ->comment('Machine-friendly unique label, e.g. "phone"');

            $table->string('identifier_label')
                ->comment('What item-level ID this category uses, e.g. IMEI, Serial No.');

            // Who created it
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_categories');
    }
}
