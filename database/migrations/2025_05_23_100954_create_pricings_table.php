<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pricings', function (Blueprint $table) {
            $table->id();
            // 4-character UUID
            $table->string('uuid', 4)->unique();
            $table->string('service');
            // up to 10 digits, 2 decimal places
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricings');
    }
};
