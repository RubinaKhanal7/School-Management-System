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
        Schema::table('accounts', function (Blueprint $table) {
            $table->decimal('opening_balance', 15, 2)->unsigned()->default(0); 
            $table->enum('balance_type', ['DR', 'CR'])->default('DR');
        });
    }

    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['opening_balance', 'balance_type']);
        });
    }
};
