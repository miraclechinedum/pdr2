<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsActiveToPricingsTable extends Migration
{
    public function up()
    {
        Schema::table('pricings', function (Blueprint $table) {
            // add a boolean flag after 'slug'
            $table->boolean('is_active')
                ->default(true)
                ->after('slug');
        });
    }

    public function down()
    {
        Schema::table('pricings', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
}
