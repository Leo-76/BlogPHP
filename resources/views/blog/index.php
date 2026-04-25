<?php ob_start(); ?>

<?php if (isset($recherche)): ?>
    <div class="page-header">
        <h1>Résultats pour « <?= htmlspecialchars($recherche) ?> »</h1>
        <p><?= count($articles) ?> article(s) trouvé(s)</p>
    </div>
<?php elseif (isset($categorie)): ?>
    <div class="page-header">
        <span class="badge"><?= htmlspecialchars($categorie['nom']) ?></span>
        <h1>Articles dans cette catégorie</h1>
    </div>
<?php else: ?>
    <div class="hero">
        <span class="badge">Le Blog</span>
        <h1>Bienvenue sur BlogPHP</h1>
        <p>Tutoriels, astuces et articles sur PHP et le développement web.</p>
    </div>
<?php endif; ?>

<?php if (empty($articles)): ?>
    <div class="empty-state"><p>Aucun article trouvé.</p><a href="/" class="btn">Retour</a></div>
<?php else: ?>
    <div class="articles-grid">
        <?php foreach ($articles as $a): ?>
        <article class="article-card">
            <div class="article-card-img" style="background:<?= ['#ede9ff','#e1f5ee','#faeeda','#faece7'][($a['id']-1)%4] ?>">
                <span><?= mb_substr($a['titre'],0,1) ?></span>
            </div>
            <div class="article-card-body">
                <?php if ($a['categorie_nom']): ?>
                    <a href="/categorie/<?= $a['categorie_slug'] ?>" class="badge"><?= htmlspecialchars($a['categorie_nom']) ?></a>
                <?php endif; ?>
                <h2><a href="/article/<?= $a['slug'] ?>"><?= htmlspecialchars($a['titre']) ?></a></h2>
                <p class="extrait"><?= htmlspecialchars($a['extrait'] ?? '') ?></p>
                <div class="article-meta">
                    <span><?= date('d/m/Y', strtotime($a['created_at'])) ?></span>
                    <?php if (isset($a['nb_commentaires'])): ?>
                        <span><?= $a['nb_commentaires'] ?> commentaire(s)</span>
                    <?php endif; ?>
                </div>
                <a href="/article/<?= $a['slug'] ?>" class="btn-link">Lire la suite →</a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layout.php'; ?>
