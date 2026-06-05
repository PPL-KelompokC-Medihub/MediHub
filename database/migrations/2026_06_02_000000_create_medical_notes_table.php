<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('medical_notes', function (Blueprint $table) {
            $table->id();
            $table->string('patient_id')->nullable();
            $table->string('doctor_id')->nullable();
            $table->text('notes');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('medical_notes');
    }
};
