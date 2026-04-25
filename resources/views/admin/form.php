<?php ob_start(); ?>
<?php if ($flash = session()->getFlash('error')): ?>
    <div class="flash-error"><?= e($flash) ?></div>
<?php endif; ?>
<?php if ($errs = session()->getFlash('errors')): ?>
    <div class="flash-error">Corrigez les erreurs ci-dessous.</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="admin-form">
    <?= csrf_field() ?>
    <div class="form-row">
        <div class="form-group" style="flex:2">
            <label>Titre *</label>
            <input type="text" name="titre" required value="<?= e($article['titre'] ?? old('titre')) ?>"
                   class="<?= has_error('titre') ? 'error' : '' ?>">
            <?php if ($err = errors('titre')): ?><div class="field-error"><?= e($err) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Catégorie</label>
            <select name="categorie_id">
                <option value="">— Aucune —</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($article['categorie_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                        <?= e($cat['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label>Extrait</label>
        <input type="text" name="extrait" value="<?= e($article['extrait'] ?? '') ?>" placeholder="Court résumé...">
    </div>

    <div class="form-group">
        <label>Contenu * <small>(HTML autorisé)</small></label>
        <textarea name="contenu" required rows="14" class="<?= has_error('contenu') ? 'error' : '' ?>"><?= e($article['contenu'] ?? '') ?></textarea>
        <?php if ($err = errors('contenu')): ?><div class="field-error"><?= e($err) ?></div><?php endif; ?>
    </div>

    <div class="form-group">
        <label>Image de couverture</label>
        <div class="upload-zone" onclick="document.getElementById('img-upload').click()">
            <?php if (!empty($article['image'])): ?>
                <img src="/<?= e($article['image']) ?>" class="upload-preview" alt="Image actuelle">
                <p style="margin-top:.5rem;font-size:.85rem;color:#888">Cliquer pour changer</p>
            <?php else: ?>
                <p>📷 Cliquer pour choisir une image</p>
                <p style="font-size:.8rem;color:#aaa">JPG, PNG, WebP — max 2 Mo</p>
            <?php endif; ?>
            <input type="file" id="img-upload" name="image" accept="image/*"
                   onchange="previewImage(this)">
        </div>
        <img id="preview" class="upload-preview" style="display:none;margin-top:.5rem">
    </div>

    <div class="form-group checkbox-group">
        <label>
            <input type="checkbox" name="publie" value="1" <?= ($article['publie'] ?? 1) ? 'checked' : '' ?>>
            Publier cet article
        </label>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn">Enregistrer</button>
        <a href="/admin/articles" class="btn btn-secondary">Annuler</a>
    </div>
</form>

<?php if (!empty($commentaires)): ?>
<div class="admin-comments" style="margin-top:2rem">
    <h2>Commentaires (<?= count($commentaires) ?>)</h2>
    <?php foreach ($commentaires as $c): ?>
    <div class="admin-comment <?= $c['approuve'] ? 'approved' : 'pending' ?>">
        <div class="comment-info">
            <strong><?= e($c['auteur']) ?></strong>
            <span><?= e($c['email']) ?></span>
            <span class="date"><?= date('d/m/Y', strtotime($c['created_at'])) ?></span>
            <?php if (!$c['approuve']): ?><span class="status-badge draft">En attente</span><?php endif; ?>
        </div>
        <p><?= nl2br(e($c['contenu'])) ?></p>
        <div class="comment-actions">
            <?php if (!$c['approuve']): ?>
            <form method="POST" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="approuver_commentaire" value="<?= $c['id'] ?>">
                <button type="submit" class="btn-sm">Approuver</button>
            </form>
            <?php endif; ?>
            <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer ?')">
                <?= csrf_field() ?>
                <input type="hidden" name="supprimer_commentaire" value="<?= $c['id'] ?>">
                <button type="submit" class="btn-sm btn-danger">Supprimer</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
function previewImage(input) {
    const preview = document.getElementById('preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/admin/layout.php'; ?>
