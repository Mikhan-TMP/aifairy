
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Server Status - AI Fairy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="/favicon.ico">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #141314ff 0%, #000000ff 100%);
            font-family: 'Montserrat', Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: rgba(255,255,255,0.95);
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(44,62,80,0.18);
            padding: 48px 32px;
            text-align: center;
            max-width: 400px;
        }
        .status {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2575fc;
            margin-bottom: 16px;
        }
        .desc {
            font-size: 1.1rem;
            color: #444;
            margin-bottom: 32px;
        }
        .pulse {
            width: 24px;
            height: 24px;
            background: #2d9c11ff;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 #6a11cb;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 #65cb11ff; }
            70% { box-shadow: 0 0 0 16px rgba(106,17,203,0); }
            100% { box-shadow: 0 0 0 0 rgba(106,17,203,0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="pulse"></div>
        <div class="status">Server is Running</div>
        <div class="desc">Welcome to <strong>AI Fairy</strong>!<br>Your server is up and ready to go.</div>
        <hr style="margin:32px 0 24px 0; border:none; border-top:1px solid #eee;">
        <?php if (isset($dbStatus) && $dbStatus): ?>
            <div style="color:#2d9c11ff; font-weight:600; font-size:1.1rem;">
                <svg width="18" height="18" style="vertical-align:middle;margin-right:6px;" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" fill="#2d9c11ff"/><path d="M7 13l3 3 7-7" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Database Connected
            </div>
        <?php else: ?>
            <div style="color:#cb1111; font-weight:600; font-size:1.1rem;">
                <svg width="18" height="18" style="vertical-align:middle;margin-right:6px;" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" fill="#cb1111"/><path d="M8 8l8 8M16 8l-8 8" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Database Not Connected
                <?php if (!empty($dbError)): ?>
                    <div style="margin-top:8px; color:#b30000; font-size:0.95rem; background:#fff0f0; border-radius:6px; padding:8px;">
                        <strong>Error:</strong> <?= htmlspecialchars($dbError) ?>
                    </div>
                    <div style="margin-top:8px; color:#b30000; font-size:0.95rem; background:#fff0f0; border-radius:6px; padding:8px;">
                        <strong>status:</strong> <?= htmlspecialchars($dbStatus) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>
