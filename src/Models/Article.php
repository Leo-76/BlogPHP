<?php
namespace App\Models;

use App\Database\Model;
use App\Database\DB;

class Article extends Model
{
    protected static string $table = 'articles';

    public static function published(): array
    {
        return DB::query('
            SELECT a.*, c.nom as categorie_nom, c.slug as categorie_slug,
                   (SELECT COUNT(*) FROM commentaires WHERE article_id = a.id AND approuve = 1) as nb_commentaires
            FROM articles a
            LEFT JOIN categories c ON a.categorie_id = c.id
            WHERE a.publie = 1
            ORDER BY a.created_at DESC
        ');
    }

    public static function findBySlug(string $slug): array|false
    {
        return DB::find('
            SELECT a.*, c.nom as categorie_nom, c.slug as categorie_slug
            FROM articles a
            LEFT JOIN categories c ON a.categorie_id = c.id
            WHERE a.slug = ? AND a.publie = 1
        ', [$slug]);
    }

    public static function byCategory(int $categoryId): array
    {
        return DB::query('
            SELECT a.*, c.nom as categorie_nom, c.slug as categorie_slug
            FROM articles a LEFT JOIN categories c ON a.categorie_id = c.id
            WHERE a.categorie_id = ? AND a.publie = 1
            ORDER BY a.created_at DESC
        ', [$categoryId]);
    }

    public static function search(string $q): array
    {
        $like = '%' . $q . '%';
        return DB::query('
            SELECT a.*, c.nom as categorie_nom, c.slug as categorie_slug
            FROM articles a LEFT JOIN categories c ON a.categorie_id = c.id
            WHERE (a.titre LIKE ? OR a.extrait LIKE ? OR a.contenu LIKE ?) AND a.publie = 1
            ORDER BY a.created_at DESC
        ', [$like, $like, $like]);
    }

    public static function makeSlug(string $titre): string
    {
        $slug = mb_strtolower($titre, 'UTF-8');
        $slug = str_replace(['à','â','é','è','ê','ë','î','ï','ô','ù','û','ü','ç'],
                            ['a','a','e','e','e','e','i','i','o','u','u','u','c'], $slug);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', trim($slug));
        $existing = DB::find('SELECT id FROM articles WHERE slug = ?', [$slug]);
        return $existing ? $slug . '-' . time() : $slug;
    }
}
