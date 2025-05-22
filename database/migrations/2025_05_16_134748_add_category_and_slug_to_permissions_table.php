<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryAndSlugToPermissionsTable extends Migration
{
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('slug')->after('name')->unique();
            $table->string('category')->after('slug');
        });
    }

    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['slug', 'category']);
        });
    }
}
