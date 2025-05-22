<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfileFieldsToUsersTable extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // after email
            $table->string('nin')->nullable()->after('email')->comment('National Insurance Number');
            $table->string('phone_number')->nullable()->after('nin')->comment('User phone number');
            $table->string('address')->nullable()->after('phone_number')->comment('User address');

            // assuming you want to reference lgas and states
            $table->foreignId('lga_id')
                ->nullable()
                ->after('address')
                ->constrained('lgas')
                ->onDelete('set null')
                ->comment('References lgas.id');

            $table->foreignId('state_id')
                ->nullable()
                ->after('lga_id')
                ->constrained('states')
                ->onDelete('set null')
                ->comment('References states.id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('state_id');
            $table->dropConstrainedForeignId('lga_id');
            $table->dropColumn(['address', 'phone_number', 'nin']);
        });
    }
}
