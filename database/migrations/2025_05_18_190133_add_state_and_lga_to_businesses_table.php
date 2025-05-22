<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStateAndLgaToBusinessesTable extends Migration
{
    public function up()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('address')->after('phone');
            $table->unsignedBigInteger('state_id')->nullable()->after('address');
            $table->unsignedBigInteger('lga_id')->nullable()->after('state_id');

            $table->foreign('state_id')
                ->references('id')->on('states')
                ->onDelete('set null');

            $table->foreign('lga_id')
                ->references('id')->on('lgas')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropForeign(['lga_id']);
            $table->dropForeign(['state_id']);
            $table->dropColumn(['lga_id', 'state_id']);
        });
    }
}
