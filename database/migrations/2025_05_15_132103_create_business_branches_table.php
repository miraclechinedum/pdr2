<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessBranchesTable extends Migration
{
    public function up(): void
    {
        Schema::create('business_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')
                ->constrained('businesses')
                ->onDelete('cascade')
                ->comment('References businesses.id');
            $table->string('address')->comment('Branch address');
            $table->foreignId('lga_id')
                ->constrained('lgas')
                ->onDelete('restrict')
                ->comment('References lgas.id');
            $table->foreignId('state_id')
                ->constrained('states')
                ->onDelete('restrict')
                ->comment('References states.id');
            $table->string('status')->default('active')->comment('Branch status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_branches');
    }
}
