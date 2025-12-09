<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * 不承認通知メール（ワーカー向け）
 */
class ApplicationRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * 新しいメッセージインスタンスを作成
     */
    public function __construct(
        public JobApplication $application
    ) {
        //
    }

    /**
     * メッセージエンベロープを取得
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '応募結果のお知らせ',
        );
    }

    /**
     * メッセージコンテンツ定義を取得
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.application-rejected',
        );
    }

    /**
     * メッセージの添付ファイルを取得
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
