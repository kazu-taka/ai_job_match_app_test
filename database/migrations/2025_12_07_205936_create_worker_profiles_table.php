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
        Schema::create('worker_profiles', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained('users')->cascadeOnDelete();
            $table->enum('gender', ['male', 'female', 'other']);
            $table->date('birthdate');
            $table->text('skills')->nullable();
            $table->text('experiences')->nullable();
            $table->string('desired_jobs')->nullable();
            $table->foreignId('desired_location_id')->nullable()->constrained('locations')->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_profiles');
    }
};
