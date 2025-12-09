<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新しい応募がありました</title>
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
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        h1 {
            color: #1e40af;
            font-size: 24px;
            margin: 0;
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

        .motive {
            background-color: #f9fafb;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 15px 0;
            white-space: pre-wrap;
        }

        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #3b82f6;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
            font-weight: bold;
        }

        .button:hover {
            background-color: #2563eb;
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
            <h1>新しい応募がありました</h1>
        </div>

        <p>貴社の求人に新しい応募がありました。</p>

        <div class="info-section">
            <div class="label">ワーカー名</div>
            <div class="value">{{ $application->worker->name }}</div>

            <div class="label">求人タイトル</div>
            <div class="value">{{ $application->jobPost->title }}</div>

            <div class="label">応募日</div>
            <div class="value">{{ $application->applied_at->format('Y年m月d日 H:i') }}</div>

            @if ($application->motive)
                <div class="label">志望動機</div>
                <div class="motive">{{ $application->motive }}</div>
            @endif
        </div>

        <a href="{{ route('applications.show', $application) }}" class="button">
            応募詳細を確認する
        </a>

        <div class="footer">
            <p>このメールは自動送信されています。ご返信いただいても対応できません。</p>
        </div>
    </div>
</body>

</html>
