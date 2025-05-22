<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBranchNameAndUserIdToBusinessBranchesTable extends Migration
{
    public function up()
    {
        Schema::table('business_branches', function (Blueprint $table) {
            // add branch_name right after uuid
            $table->string('branch_name')->after('uuid');
            // add user_id (creator) after status
            $table->unsignedBigInteger('user_id')
                ->after('status')
                ->nullable(); // or ->nullable(false) if required

            // foreign key constraint
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::table('business_branches', function (Blueprint $table) {
            // drop foreign first
            $table->dropForeign(['user_id']);
            // then drop the columns
            $table->dropColumn('user_id');
            $table->dropColumn('branch_name');
        });
    }
}
