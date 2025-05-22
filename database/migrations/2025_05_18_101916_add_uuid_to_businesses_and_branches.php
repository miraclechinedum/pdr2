<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUuidToBusinessesAndBranches extends Migration
{
    public function up()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->unique()->nullable(false);
        });

        Schema::table('business_branches', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->unique()->nullable(false);
        });
    }

    public function down()
    {
        Schema::table('business_branches', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
}
