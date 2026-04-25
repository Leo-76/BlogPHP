<?php ob_start(); ?>
<div class="login-box">
    <div class="login-logo">
        <span class="logo-icon">B</span>
        <h1>Inscription</h1>
    </div>
    <?php if ($flash = session()->getFlash('error')): ?>
        <div class="flash-error"><?= e($flash) ?></div>
    <?php endif; ?>

    <form method="POST" action="/register">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Nom</label>
            <input type="text" name="nom" value="<?= old('nom') ?>" required placeholder="Jean Dupont"
                   class="<?= has_error('nom') ? 'error' : '' ?>">
            <?php if ($err = errors('nom')): ?><div class="field-error"><?= e($err) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= old('email') ?>" required placeholder="jean@email.com"
                   class="<?= has_error('email') ? 'error' : '' ?>">
            <?php if ($err = errors('email')): ?><div class="field-error"><?= e($err) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="password" required placeholder="••••••••" minlength="8"
                   class="<?= has_error('password') ? 'error' : '' ?>">
            <?php if ($err = errors('password')): ?><div class="field-error"><?= e($err) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Confirmer le mot de passe</label>
            <input type="password" name="password_confirmation" required placeholder="••••••••">
            <?php if ($err = errors('password_confirmation')): ?><div class="field-error"><?= e($err) ?></div><?php endif; ?>
        </div>
        <button type="submit" class="btn" style="width:100%">S'inscrire</button>
    </form>
    <p style="text-align:center;margin-top:1rem;font-size:.85rem">
        Déjà un compte ? <a href="/login">Se connecter</a>
    </p>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/auth/layout.php'; ?>
