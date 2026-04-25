<?php ob_start(); ?>
<?php if ($flash = session()->getFlash('success')): ?>
    <div class="flash-success"><?= e($flash) ?></div>
<?php endif; ?>
<div class="admin-toolbar">
    <a href="/admin/article/new" class="btn">+ Nouvel article</a>
</div>
<div class="admin-table-wrap">
<table class="admin-table">
    <thead>
        <tr><th>Titre</th><th>Catégorie</th><th>Statut</th><th>Date</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($articles->items as $a): ?>
        <tr>
            <td><a href="/article/<?= $a['slug'] ?>" target="_blank"><?= e($a['titre']) ?></a></td>
            <td><?= e($a['categorie_nom'] ?? '—') ?></td>
            <td><span class="status-badge <?= $a['publie'] ? 'published' : 'draft' ?>"><?= $a['publie'] ? 'Publié' : 'Brouillon' ?></span></td>
            <td><?= date('d/m/Y', strtotime($a['created_at'])) ?></td>
            <td class="actions">
                <a href="/admin/article/<?= $a['id'] ?>" class="btn-sm">Modifier</a>
                <form method="POST" action="/admin/article/<?= $a['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Supprimer ?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-sm btn-danger">Supprimer</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?= $articles->links() ?>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/admin/layout.php'; ?>
