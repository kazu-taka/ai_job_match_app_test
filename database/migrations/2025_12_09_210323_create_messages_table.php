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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_room_id')
                ->constrained('chat_rooms')
                ->onDelete('cascade')
                ->comment('チャットルームID');
            $table->foreignId('sender_id')
                ->constrained('users')
                ->onDelete('restrict')
                ->comment('送信者ID');
            $table->text('message')->comment('メッセージ内容');
            $table->boolean('is_read')->default(false)->comment('既読フラグ');
            $table->timestamps();

            // チャットルームIDにインデックスを追加（検索高速化）
            $table->index('chat_room_id');
        });
    }

    /**
     * マイグレーションの巻き戻し
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
