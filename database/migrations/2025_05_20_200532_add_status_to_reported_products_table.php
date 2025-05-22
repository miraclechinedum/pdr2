<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToReportedProductsTable extends Migration
{
    public function up()
    {
        Schema::table('reported_products', function (Blueprint $table) {
            $table->boolean('status')
                ->after('description')
                ->default(true)
                ->comment('true = active report, false = resolved');
        });
    }

    public function down()
    {
        Schema::table('reported_products', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
