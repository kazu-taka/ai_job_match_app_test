<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>応募が承認されました</title>
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
            border-bottom: 3px solid #10b981;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        h1 {
            color: #059669;
            font-size: 24px;
            margin: 0;
        }

        .success-badge {
            display: inline-block;
            background-color: #d1fae5;
            color: #065f46;
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
            background-color: #ecfdf5;
            border-left: 4px solid #10b981;
            padding: 15px;
            margin: 15px 0;
        }

        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #10b981;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
            font-weight: bold;
        }

        .button:hover {
            background-color: #059669;
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
            <h1>おめでとうございます！</h1>
        </div>

        <div class="success-badge">応募が承認されました</div>

        <div class="message">
            <p>ご応募いただいた求人について、企業様より承認の連絡がありました。おめでとうございます！</p>
        </div>

        <div class="info-section">
            <div class="label">企業名</div>
            <div class="value">{{ $application->jobPost->company->name }}</div>

            <div class="label">求人タイトル</div>
            <div class="value">{{ $application->jobPost->title }}</div>

            <div class="label">承認日</div>
            <div class="value">{{ $application->judged_at->format('Y年m月d日 H:i') }}</div>
        </div>

        <p>詳細につきましては、企業様から別途ご連絡がある場合があります。今後の流れについては企業様の指示をお待ちください。</p>

        <a href="{{ route('applications.show', $application) }}" class="button">
            応募詳細を確認する
        </a>

        <div class="footer">
            <p>このメールは自動送信されています。ご返信いただいても対応できません。</p>
        </div>
    </div>
</body>

</html>
