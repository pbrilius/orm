<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Not Found</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 600px; margin: 0 auto; padding: 2rem; text-align: center; }
        h1 { color: #c00; }
        a { color: #0066cc; }
    </style>
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <p><?= htmlspecialchars($message ?? 'The requested page could not be found.') ?></p>
    <p><a href="/">← Return to Home</a></p>
</body>
</html>
