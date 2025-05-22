<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessesTable extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            // If each business has an owner (user)
            $table->foreignId('owner_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('References users.id who owns this business');
            $table->string('name')->comment('Business name');
            $table->string('email')->nullable()->comment('Business contact email');
            $table->string('phone')->nullable()->comment('Business contact phone');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
}
