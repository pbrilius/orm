<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Oryx ORM</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 800px; margin: 0 auto; padding: 2rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #ddd; padding: 0.75rem; text-align: left; }
        th { background: #f5f5f5; }
        a { color: #0066cc; }
    </style>
</head>
<body>
    <h1>Users</h1>
    <p><a href="/users/create">+ Add New User</a></p>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Roles</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user->getId() ?></td>
                <td><?= htmlspecialchars($user->getEmail()) ?></td>
                <td><?= htmlspecialchars(implode(', ', $user->getRoles())) ?></td>
                <td><?= $user->getCreatedAt()->format('Y-m-d') ?></td>
                <td>
                    <a href="/users/<?= $user->getId() ?>">View</a> |
                    <a href="/users/<?= $user->getId() ?>/edit">Edit</a> |
                    <a href="/users/<?= $user->getId() ?>/delete" onclick="return confirm('Delete?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <p><a href="/">← Back to Home</a></p>
</body>
</html>
