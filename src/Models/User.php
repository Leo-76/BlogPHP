<?php
namespace App\Models;

use App\Database\Model;

class User extends Model
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): array|false
    {
        return static::findBy('email', $email);
    }

    public static function verifyPassword(array $user, string $password): bool
    {
        return password_verify($password, $user['password']);
    }
}
