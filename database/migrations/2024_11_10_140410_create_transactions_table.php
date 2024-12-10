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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('voucher_no');
            $table->text('description')->nullable();
            $table->foreignId('voucher_type_id')->constrained('voucher_types');
            $table->tinyInteger('status')->default(1); 
            $table->date('transaction_date_eng');
            $table->string('transaction_date_nepali');
            $table->foreignId('fiscal_year_id')->constrained('fiscal_years');
            $table->double('transaction_amount')->default(0);
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
        Schema::dropIfExists('transactions');
    }
};
