<?php ob_start(); ?>
<div class="error-page">
    <div class="error-code">404</div>
    <h1>Page introuvable</h1>
    <p>L'article ou la page que vous cherchez n'existe pas.</p>
    <a href="/" class="btn">Retour à l'accueil</a>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layout.php'; ?>
