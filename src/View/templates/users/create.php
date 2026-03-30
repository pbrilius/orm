<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User - Oryx ORM</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 500px; margin: 0 auto; padding: 2rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.25rem; font-weight: bold; }
        input, select { width: 100%; padding: 0.5rem; box-sizing: border-box; }
        button { background: #0066cc; color: white; padding: 0.75rem 1.5rem; border: none; cursor: pointer; }
        button:hover { background: #0055aa; }
    </style>
</head>
<body>
    <h1>Create User</h1>
    
    <form method="POST" action="/users/create">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group">
            <label for="roles">Roles:</label>
            <select id="roles" name="roles[]" multiple>
                <option value="ROLE_USER" selected>ROLE_USER</option>
                <option value="ROLE_ADMIN">ROLE_ADMIN</option>
                <option value="ROLE_EDITOR">ROLE_EDITOR</option>
            </select>
        </div>
        
        <button type="submit">Create User</button>
        <a href="/users">Cancel</a>
    </form>
</body>
</html>
