<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToReceiptProducts extends Migration
{
    public function up()
    {
        Schema::table('receipt_products', function (Blueprint $table) {
            $table->string('status')->default('active')->after('quantity');
        });
    }

    public function down()
    {
        Schema::table('receipt_products', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
