<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Oryx ORM') ?></title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 800px; margin: 0 auto; padding: 2rem; }
        h1 { color: #333; }
        nav { margin: 1rem 0; padding: 1rem; background: #f5f5f5; }
        nav a { margin-right: 1rem; color: #0066cc; }
        .info { background: #e7f3ff; padding: 1rem; border-radius: 4px; }
    </style>
</head>
<body>
    <h1><?= htmlspecialchars($title ?? 'Oryx ORM') ?></h1>
    
    <nav>
        <a href="/">Home</a>
        <a href="/users">Users</a>
        <a href="/api/users">API (JSON)</a>
    </nav>
    
    <div class="info">
        <h2>Full-Stack PHP Application</h2>
        <p>This application demonstrates multiple architectural patterns:</p>
        <ul>
            <li><strong>MVC</strong> - Traditional server-side rendering (this page)</li>
            <li><strong>ADR</strong> - API endpoints for SPAs (<a href="/api/users">/api/users</a>)</li>
            <li><strong>PWA</strong> - Progressive Web App ready</li>
        </ul>
    </div>
    
    <?php if (isset($description)): ?>
    <p><?= htmlspecialchars($description) ?></p>
    <?php endif; ?>
</body>
</html>
