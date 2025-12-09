<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>応募結果のお知らせ</title>
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
            border-bottom: 3px solid #6b7280;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        h1 {
            color: #374151;
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

        .message {
            background-color: #f9fafb;
            border-left: 4px solid #6b7280;
            padding: 15px;
            margin: 15px 0;
        }

        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #6b7280;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
            font-weight: bold;
        }

        .button:hover {
            background-color: #4b5563;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }

        .encouragement {
            background-color: #dbeafe;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>応募結果のお知らせ</h1>
        </div>

        <div class="message">
            <p>この度はご応募いただき、誠にありがとうございました。</p>
            <p>慎重に選考を進めてまいりましたが、誠に残念ながら今回は見送りとさせていただくこととなりました。</p>
        </div>

        <div class="info-section">
            <div class="label">企業名</div>
            <div class="value">{{ $application->jobPost->company->name }}</div>

            <div class="label">求人タイトル</div>
            <div class="value">{{ $application->jobPost->title }}</div>

            <div class="label">判定日</div>
            <div class="value">{{ $application->judged_at->format('Y年m月d日 H:i') }}</div>
        </div>

        <div class="encouragement">
            <p><strong>他にも多くの求人があります</strong></p>
            <p>今回は残念な結果となりましたが、あなたに合った求人がきっと見つかります。引き続き、他の求人もご検討ください。</p>
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
