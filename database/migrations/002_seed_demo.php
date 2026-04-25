<?php
use Doctrine\DBAL\Connection;

return new class {
    public function up(Connection $conn): void
    {
        if ($conn->fetchOne('SELECT COUNT(*) FROM articles') > 0) return;

        foreach ([['PHP','php'],['Web','web'],['Tutoriels','tutoriels']] as [$nom,$slug]) {
            $conn->insert('categories', compact('nom','slug'));
        }

        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->insert('users', ['nom'=>'Admin','email'=>'admin@blog.com','password'=>$password,'role'=>'admin','actif'=>1]);

        $articles = [
            ['Créer un framework PHP maison','creer-framework-php','Construire son propre framework PHP.',
             '<p>Un exercice excellent pour comprendre Symfony ou Laravel.</p>',1],
            ['PHP 8 — Les nouvelles fonctionnalités','php8-nouveautes','JIT, match, union types.',
             '<p>PHP 8 apporte des améliorations majeures de syntaxe et performance.</p>',1],
            ['Introduction à SQLite avec PHP','sqlite-php','Base de données légère, zéro config.',
             '<p>SQLite est parfaite pour les petits projets PHP.</p>',3],
        ];

        foreach ($articles as [$titre,$slug,$extrait,$contenu,$cat_id]) {
            $conn->insert('articles', compact('titre','slug','extrait','contenu') + ['categorie_id'=>$cat_id]);
        }

        $conn->insert('commentaires', ['article_id'=>1,'auteur'=>'Marie','email'=>'marie@ex.com','contenu'=>'Super article !','approuve'=>1]);
        $conn->insert('commentaires', ['article_id'=>2,'auteur'=>'Paul','email'=>'paul@ex.com','contenu'=>'Très utile merci !','approuve'=>1]);
    }

    public function down(Connection $conn): void {}
};
