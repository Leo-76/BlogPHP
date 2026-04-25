<?php
namespace App\Models;

use App\Database\Model;
use App\Database\DB;

class Commentaire extends Model
{
    protected static string $table = 'commentaires';

    public static function forArticle(int $articleId): array
    {
        return DB::query(
            'SELECT * FROM commentaires WHERE article_id = ? AND approuve = 1 ORDER BY created_at ASC',
            [$articleId]
        );
    }

    public static function pending(): array
    {
        return DB::query('SELECT c.*, a.titre as article_titre FROM commentaires c JOIN articles a ON c.article_id = a.id WHERE c.approuve = 0 ORDER BY c.created_at DESC');
    }
}
