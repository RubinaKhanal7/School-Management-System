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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->foreign('school_id')->references('id')->on('schools');
            $table->unsignedBigInteger('inventory_head_id');
            $table->foreign('inventory_head_id')->references('id')->on('inventory_head');
            $table->string('name');
            $table->string('condition');
            $table->string('costprice');
            $table->string('tax')->nullable();
            $table->string('specs_details')->nullable();
            $table->string('guess_life')->nullable();

            $table->unsignedBigInteger('sources_id');
            $table->foreign('sources_id')->references('id')->on('sources')->onDelete('cascade');
            
            $table->string('tax_free_amount')->nullable();
            $table->string('tax_free_details')->nullable();
            $table->string('depreciation_percentage')->nullable();
            $table->text('other_details')->nullable();

            $table->string('land_area')->nullable();
            $table->string('land_type')->nullable();
            $table->string('land_costprice')->nullable();
            $table->string('land_owner_certificate_no')->nullable();
            $table->string('land_location')->nullable();
            $table->string('land_kitta_no')->nullable();
            $table->boolean('if_donation')->default(0)->comment('0=>no, 1=>yes')->nullable();
            $table->string('land_market_value')->nullable();
            $table->boolean('if_physical_structure_there')->default(0)->comment('0=>no, 1=>yes')->nullable();
            $table->text('physical_structure_detail')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};