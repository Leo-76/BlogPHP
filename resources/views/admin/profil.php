<?php ob_start(); ?>
<?php if ($flash = session()->getFlash('success')): ?>
    <div class="flash-success"><?= e($flash) ?></div>
<?php endif; ?>

<form method="POST" action="/admin/profil" enctype="multipart/form-data" class="admin-form">
    <?= csrf_field() ?>

    <div style="display:flex;align-items:center;gap:1.5rem;margin-bottom:1.5rem">
        <?php if ($user['avatar']): ?>
            <img src="/<?= e($user['avatar']) ?>" alt="Avatar" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid #e0e0e0">
        <?php else: ?>
            <div style="width:80px;height:80px;border-radius:50%;background:#f0f0f0;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:800;color:#505050">
                <?= mb_strtoupper(mb_substr($user['nom'], 0, 1)) ?>
            </div>
        <?php endif; ?>
        <div>
            <label class="btn btn-secondary" style="cursor:pointer">
                Changer l'avatar
                <input type="file" name="avatar" accept="image/*" style="display:none">
            </label>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Nom</label>
            <input type="text" name="nom" value="<?= e($user['nom']) ?>" required>
            <?php if ($err = errors('nom')): ?><div class="field-error"><?= e($err) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= e($user['email']) ?>" required>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Nouveau mot de passe <small>(laisser vide pour ne pas changer)</small></label>
            <input type="password" name="password" placeholder="••••••••" minlength="8">
        </div>
        <div class="form-group">
            <label>Confirmer le mot de passe</label>
            <input type="password" name="password_confirmation" placeholder="••••••••">
        </div>
    </div>

    <div class="form-group">
        <label>Token API</label>
        <div class="api-token"><?= e($user['api_token'] ?? 'Aucun token généré') ?></div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn">Enregistrer</button>
        <a href="/admin" class="btn btn-secondary">Annuler</a>
    </div>
</form>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/admin/layout.php'; ?>
