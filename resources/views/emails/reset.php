<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">
<style>body{font-family:sans-serif;background:#f5f5f5;padding:2rem}.box{background:#fff;max-width:500px;margin:0 auto;border-radius:12px;padding:2rem}.btn{display:inline-block;background:#505050;color:#fff;padding:.75rem 2rem;border-radius:8px;text-decoration:none;font-weight:600;margin-top:1rem}</style>
</head><body><div class="box">
<h2>Réinitialisation de mot de passe</h2>
<p>Bonjour <?= e($user['nom']) ?>, cliquez pour réinitialiser votre mot de passe :</p>
<a href="<?= env('APP_URL') ?>/reset/<?= e($token) ?>" class="btn">Réinitialiser</a>
<p style="margin-top:1rem;font-size:.8rem;color:#888">Ce lien expire dans 1h.</p>
</div></body></html>
