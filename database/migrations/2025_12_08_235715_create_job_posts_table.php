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
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->unsignedBigInteger('employment_type_id');
            $table->unsignedBigInteger('work_style_id');
            $table->unsignedBigInteger('industry_id');
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $table->string('working_hours');
            $table->integer('salary');
            $table->integer('number_of_positions');
            $table->dateTime('posted_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};
