<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatesTable extends Migration
{
    public function up(): void
    {
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('State name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('states');
    }
}
