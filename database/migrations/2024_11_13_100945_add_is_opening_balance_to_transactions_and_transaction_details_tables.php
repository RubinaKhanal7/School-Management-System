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
        Schema::table('transactions', function (Blueprint $table) {
            $table->tinyInteger('is_opening_balance')->default(0);
        });

        Schema::table('transaction_details', function (Blueprint $table) {
            $table->tinyInteger('is_opening_balance')->default(0);
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('is_opening_balance');
        });

        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropColumn('is_opening_balance');
        });
    }
};
