<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('rc_number')->nullable()->after('owner_id');
            $table->renameColumn('name', 'business_name');
        });
    }

    public function down()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('rc_number');
            $table->renameColumn('business_name', 'name');
        });
    }
};
