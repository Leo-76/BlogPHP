<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — <?= e($titre ?? '') ?> — BlogPHP</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body class="admin-body">
<div class="admin-wrap">
    <aside class="admin-sidebar">
        <div class="admin-logo">
            <span class="logo-icon">B</span>
            <span>Admin</span>
        </div>
        <nav class="admin-nav">
            <a href="/admin">📊 Dashboard</a>
            <a href="/admin/articles">📝 Articles</a>
            <a href="/admin/article/new">+ Nouvel article</a>
            <hr>
            <a href="/admin/profil">👤 Mon profil</a>
            <a href="/" target="_blank">🌐 Voir le blog</a>
            <a href="/logout" class="nav-logout">🚪 Déconnexion</a>
        </nav>
    </aside>
    <main class="admin-main">
        <div class="admin-header">
            <h1><?= e($titre ?? '') ?></h1>
            <span style="font-size:.9rem;color:#888">
                Bonjour, <strong><?= e($_SESSION['user_nom'] ?? 'Admin') ?></strong>
            </span>
        </div>
        <?= $content ?? '' ?>
    </main>
</div>
</body>
</html>
