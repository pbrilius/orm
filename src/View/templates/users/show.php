<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User #<?= $user->getId() ?> - Oryx ORM</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 600px; margin: 0 auto; padding: 2rem; }
        .field { margin-bottom: 0.5rem; }
        .label { font-weight: bold; }
        a { color: #0066cc; }
    </style>
</head>
<body>
    <h1>User #<?= $user->getId() ?></h1>
    
    <div class="field">
        <span class="label">Email:</span>
        <?= htmlspecialchars($user->getEmail()) ?>
    </div>
    
    <div class="field">
        <span class="label">Roles:</span>
        <?= htmlspecialchars(implode(', ', $user->getRoles())) ?>
    </div>
    
    <div class="field">
        <span class="label">Created:</span>
        <?= $user->getCreatedAt()->format('Y-m-d H:i:s') ?>
    </div>
    
    <?php if ($user->getUpdatedAt()): ?>
    <div class="field">
        <span class="label">Updated:</span>
        <?= $user->getUpdatedAt()->format('Y-m-d H:i:s') ?>
    </div>
    <?php endif; ?>
    
    <p>
        <a href="/users/<?= $user->getId() ?>/edit">Edit</a> |
        <a href="/users">← Back to List</a>
    </p>
</body>
</html>
