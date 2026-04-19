<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('umur')->nullable();
            $table->string('email')->nullable();
            $table->string('weight')->nullable();
            $table->string('height')->nullable();
            $table->string('gender')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('code_pos')->nullable();
            $table->timestamps();
        });

        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('umur')->nullable();
            $table->string('email')->nullable();
            $table->string('weight')->nullable();
            $table->string('height')->nullable();
            $table->string('gender')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('allergy_history')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('code_pos')->nullable();
            $table->timestamps();
        });

        Schema::create('doctor_specializations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->string('main_specialization')->nullable();
            $table->string('sub_specialization')->nullable();
            $table->string('practice_year')->nullable();
            $table->string('academy')->nullable();
            $table->string('service')->nullable();
            $table->text('short_biography')->nullable();
            $table->timestamps();
        });

        Schema::create('doctor_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->string('certification1')->nullable();
            $table->string('certification2')->nullable();
            $table->string('certification3')->nullable();
            $table->string('certification4')->nullable();
            $table->string('certification5')->nullable();
            $table->string('certification6')->nullable();
            $table->timestamps();
        });

        Schema::create('doctor_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->string('str')->nullable();
            $table->string('sip')->nullable();
            $table->string('ijazah_doctor')->nullable();
            $table->string('ktp')->nullable();
            $table->string('profile_pict')->nullable();
            $table->timestamps();
        });

        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->date('schedule_date');
            $table->time('time_start');
            $table->time('time_end');
            $table->timestamps();
        });

        Schema::create('booking_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('doctor_schedule_id')->constrained('doctor_schedules')->cascadeOnDelete();
            $table->string('medical_doc')->nullable();
            $table->text('complaint')->nullable();
            $table->string('status')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('booking_schedule_id')->constrained('booking_schedules')->cascadeOnDelete();
            $table->text('main_complaint')->nullable();
            $table->text('observation_result')->nullable();
            $table->text('psychological_assessment')->nullable();
            $table->text('conclusion')->nullable();
            $table->text('recommendation')->nullable();
            $table->timestamps();
        });

        Schema::create('review_doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('review_text')->nullable();
            $table->timestamps();
        });

        Schema::create('review_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_doctor_id')->constrained('review_doctors')->cascadeOnDelete();
            $table->unsignedInteger('like_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_likes');
        Schema::dropIfExists('review_doctors');
        Schema::dropIfExists('diagnoses');
        Schema::dropIfExists('booking_schedules');
        Schema::dropIfExists('doctor_schedules');
        Schema::dropIfExists('doctor_documents');
        Schema::dropIfExists('doctor_certifications');
        Schema::dropIfExists('doctor_specializations');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('doctors');
    }
};
