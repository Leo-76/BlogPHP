<?php ob_start(); ?>
<div class="login-box">
    <div class="login-logo">
        <span class="logo-icon">B</span>
        <h1>Nouveau mot de passe</h1>
    </div>
    <?php if ($flash = session()->getFlash('error')): ?>
        <div class="flash-error"><?= e($flash) ?></div>
    <?php endif; ?>
    <form method="POST" action="/reset/<?= e($token) ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Nouveau mot de passe</label>
            <input type="password" name="password" required placeholder="••••••••" minlength="8">
            <?php if ($err = errors('password')): ?><div class="field-error"><?= e($err) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Confirmer</label>
            <input type="password" name="password_confirmation" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn" style="width:100%">Changer le mot de passe</button>
    </form>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/auth/layout.php'; ?>
