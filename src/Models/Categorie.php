<?php
namespace App\Models;

use App\Database\Model;

class Categorie extends Model
{
    protected static string $table = 'categories';

    public static function findBySlug(string $slug): array|false
    {
        return static::findBy('slug', $slug);
    }
}
