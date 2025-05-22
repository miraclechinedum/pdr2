<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessStaffsTable extends Migration
{
    public function up(): void
    {
        Schema::create('business_staffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')
                ->constrained('businesses')
                ->onDelete('cascade')
                ->comment('References businesses.id');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('References users.id (staff member)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_staffs');
    }
}
