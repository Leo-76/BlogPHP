<?php ob_start(); ?>
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?= $stats['articles'] ?></div>
        <div class="stat-label">Articles</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $stats['commentaires'] ?></div>
        <div class="stat-label">Commentaires en attente</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $stats['categories'] ?></div>
        <div class="stat-label">Catégories</div>
    </div>
</div>
<div class="admin-actions">
    <a href="/admin/article/new" class="btn">+ Nouvel article</a>
    <a href="/admin/articles" class="btn btn-secondary">Voir tous les articles</a>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/admin/layout.php'; ?>
