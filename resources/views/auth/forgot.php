<?php ob_start(); ?>
<div class="login-box">
    <div class="login-logo">
        <span class="logo-icon">B</span>
        <h1>Mot de passe oublié</h1>
    </div>
    <?php if ($flash = session()->getFlash('success')): ?>
        <div class="flash-success"><?= e($flash) ?></div>
    <?php endif; ?>
    <form method="POST" action="/forgot-password">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Adresse email</label>
            <input type="email" name="email" required placeholder="jean@email.com">
        </div>
        <button type="submit" class="btn" style="width:100%">Envoyer le lien</button>
    </form>
    <p style="text-align:center;margin-top:1rem;font-size:.85rem">
        <a href="/login">← Retour à la connexion</a>
    </p>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/auth/layout.php'; ?>
