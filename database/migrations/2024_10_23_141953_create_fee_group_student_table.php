<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeeGroupStudentTable extends Migration
{
    public function up()
    {
        Schema::create('fee_group_student', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_session_id');
            $table->foreign('student_session_id')->references('id')->on('student_sessions')->onDelete('cascade');
            $table->foreignId('fee_group_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fee_group_student');
    }
}
