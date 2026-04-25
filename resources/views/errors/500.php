<?php ob_start(); ?>
<div class="error-page">
    <div class="error-code">500</div>
    <h1>Erreur serveur</h1>
    <p>Une erreur inattendue s'est produite.</p>
    <a href="/" class="btn">Retour à l'accueil</a>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layout.php'; ?>
