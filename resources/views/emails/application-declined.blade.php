<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>応募が辞退されました</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, 'Hiragino Kaku Gothic ProN', 'Hiragino Sans', Meiryo, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .container {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header {
            border-bottom: 3px solid #f59e0b;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        h1 {
            color: #d97706;
            font-size: 24px;
            margin: 0;
        }

        .warning-badge {
            display: inline-block;
            background-color: #fef3c7;
            color: #92400e;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .info-section {
            margin-bottom: 25px;
        }

        .label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }

        .value {
            color: #333;
            margin-bottom: 15px;
        }

        .message {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 15px 0;
        }

        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #f59e0b;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
            font-weight: bold;
        }

        .button:hover {
            background-color: #d97706;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>応募辞退のお知らせ</h1>
        </div>

        <div class="warning-badge">応募が辞退されました</div>

        <div class="message">
            <p>貴社の求人にご応募いただいたワーカー様より、応募を辞退したいとの連絡がありました。</p>
        </div>

        <div class="info-section">
            <div class="label">ワーカー名</div>
            <div class="value">{{ $application->worker->name }}</div>

            <div class="label">求人タイトル</div>
            <div class="value">{{ $application->jobPost->title }}</div>

            <div class="label">辞退日</div>
            <div class="value">{{ $application->declined_at->format('Y年m月d日 H:i') }}</div>
        </div>

        <p>この応募は辞退済みとなり、選考を進めることはできません。</p>

        <a href="{{ route('applications.show', $application) }}" class="button">
            応募詳細を確認する
        </a>

        <div class="footer">
            <p>このメールは自動送信されています。ご返信いただいても対応できません。</p>
        </div>
    </div>
</body>

</html>
