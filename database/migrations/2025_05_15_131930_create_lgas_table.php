<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLgasTable extends Migration
{
    public function up(): void
    {
        Schema::create('lgas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('state_id')
                ->constrained('states')
                ->onDelete('cascade')
                ->comment('References states.id');
            $table->string('name')->comment('Local Government Area name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lgas');
    }
}
