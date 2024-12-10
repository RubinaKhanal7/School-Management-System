<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions');
            $table->foreignId('account_id')->constrained('accounts');
            $table->double('debit')->default(0);
            $table->double('credit')->default(0);
            $table->foreignId('fiscal_year_id')->constrained('fiscal_years');
            $table->date('transaction_date_eng');
            $table->string('transaction_date_nepali');
            $table->tinyInteger('status')->default(1); 
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};
