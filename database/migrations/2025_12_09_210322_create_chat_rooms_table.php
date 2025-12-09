<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * マイグレーションの実行
     */
    public function up(): void
    {
        Schema::create('chat_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')
                ->unique()
                ->constrained('job_applications')
                ->onDelete('cascade')
                ->comment('応募情報ID');
            $table->timestamps();
        });
    }

    /**
     * マイグレーションの巻き戻し
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_rooms');
    }
};
