<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pricings', function (Blueprint $table) {
            $table->string('slug')->after('amount')->unique();
            $table->foreignId('user_id')->after('slug')
                ->constrained('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pricings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('slug');
        });
    }
};
