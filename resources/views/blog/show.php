<?php ob_start(); ?>
<article class="article-full">
    <?php if ($article['categorie_nom']): ?>
        <a href="/categorie/<?= $article['categorie_slug'] ?>" class="badge"><?= htmlspecialchars($article['categorie_nom']) ?></a>
    <?php endif; ?>
    <h1><?= htmlspecialchars($article['titre']) ?></h1>
    <div class="article-meta">
        <span>Publié le <?= date('d/m/Y à H:i', strtotime($article['created_at'])) ?></span>
    </div>
    <div class="article-content">
        <?= $article['contenu'] ?>
    </div>
</article>
<div class="back-link"><a href="/" class="btn-link">← Retour aux articles</a></div>

<section class="commentaires" id="commentaires">
    <h2>Commentaires (<?= count($commentaires) ?>)</h2>
    <?php if (empty($commentaires)): ?>
        <p class="empty-comment">Aucun commentaire. Soyez le premier !</p>
    <?php else: ?>
        <div class="comment-list">
        <?php foreach ($commentaires as $c): ?>
            <div class="comment">
                <div class="comment-avatar"><?= mb_strtoupper(mb_substr($c['auteur'],0,1)) ?></div>
                <div class="comment-body">
                    <div class="comment-header">
                        <strong><?= htmlspecialchars($c['auteur']) ?></strong>
                        <span class="date"><?= date('d/m/Y', strtotime($c['created_at'])) ?></span>
                    </div>
                    <p><?= nl2br(htmlspecialchars($c['contenu'])) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="comment-form">
        <h3>Laisser un commentaire</h3>
        <?php if ($erreur): ?><div class="alert alert-error"><?= $erreur ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="auteur" required placeholder="Votre nom">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="votre@email.com">
                </div>
            </div>
            <div class="form-group">
                <label>Message</label>
                <textarea name="contenu" required placeholder="Votre commentaire..." rows="4"></textarea>
            </div>
            <button type="submit" class="btn">Envoyer</button>
            <p class="form-note">Votre commentaire sera visible après modération.</p>
        </form>
    </div>
</section>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layout.php'; ?>
