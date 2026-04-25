<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titre ?? 'Blog PHP') ?> — BlogPHP</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
<header>
    <div class="header-inner">
        <a href="/" class="logo">
            <span class="logo-icon">B</span>
            <span>Blog<strong>PHP</strong></span>
        </a>
        <form action="/recherche" method="GET" class="search-form">
            <input type="search" name="q" placeholder="Rechercher..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            <button type="submit">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </button>
        </form>
        <nav class="main-nav">
            <a href="/">Accueil</a>
            <?php foreach ($categories as $cat): ?>
                <a href="/categorie/<?= $cat['slug'] ?>"><?= htmlspecialchars($cat['nom']) ?></a>
            <?php endforeach; ?>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/admin" class="nav-admin">Admin</a>
                <a href="/logout" class="nav-logout">Déconnexion</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<?php if ($flash = ($_SESSION['_flash']['success'] ?? null)): unset($_SESSION['_flash']['success']); ?>
    <div class="flash-success"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>
<?php if ($flash = ($_SESSION['_flash']['error'] ?? null)): unset($_SESSION['_flash']['error']); ?>
    <div class="flash-error"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<div class="page-wrapper">
    <main class="page-main">
        <?= $content ?? '' ?>
    </main>
    <aside class="sidebar">
        <div class="widget">
            <h3 class="widget-title">Catégories</h3>
            <ul class="widget-list">
                <?php foreach ($categories as $cat): ?>
                    <li><a href="/categorie/<?= $cat['slug'] ?>"><?= htmlspecialchars($cat['nom']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="widget">
            <h3 class="widget-title">Articles récents</h3>
            <ul class="widget-list">
                <?php foreach ($recents as $r): ?>
                    <li>
                        <a href="/article/<?= $r['slug'] ?>"><?= htmlspecialchars($r['titre']) ?></a>
                        <span class="date"><?= date('d/m/Y', strtotime($r['created_at'])) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </aside>
</div>

<footer>
    <p>BlogPHP &mdash; Fait avec ♥ en PHP pur</p>
</footer>
</body>
</html>
