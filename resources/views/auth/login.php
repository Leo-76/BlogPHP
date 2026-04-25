<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body class="login-page">
    <div class="login-box">
        <div class="login-logo">
            <span class="logo-icon">B</span>
            <h1>Admin</h1>
        </div>
        <?php if ($erreur): ?>
            <div class="alert alert-error"><?= $erreur ?></div>
        <?php endif; ?>
        <form method="POST" action="/login">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="admin@blog.com" value="admin@blog.com">
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" required placeholder="••••••••" value="admin123">
            </div>
            <button type="submit" class="btn" style="width:100%">Se connecter</button>
        </form>
        <p style="text-align:center;margin-top:1rem;font-size:.85rem;color:#888">
            <a href="/">← Retour au blog</a>
        </p>
    </div>
</body>
</html>
