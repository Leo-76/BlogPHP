<?php
namespace App\Auth;

use App\Database\DB;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;

/**
 * Auth avec symfony/password-hasher
 */
class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $user = DB::find('SELECT * FROM users WHERE email = ? AND actif = 1', [$email]);
        if (!$user || !sf_hash_check($password, $user['password'])) return false;
        static::loginUser($user);
        return true;
    }

    public static function loginUser(array $user): void
    {
        $session = session();
        $session->set('user_id',    $user['id']);
        $session->set('user_nom',   $user['nom']);
        $session->set('user_email', $user['email']);
        $session->set('user_role',  $user['role'] ?? 'user');
        $session->migrate(true); // Régénère l'ID de session (sécurité)
    }

    public static function logout(): void
    {
        session()->invalidate();
    }

    public static function check(): bool   { return session()->has('user_id'); }
    public static function guest(): bool   { return !static::check(); }
    public static function id(): ?int      { return session()->get('user_id'); }
    public static function role(): string  { return session()->get('user_role', 'user'); }
    public static function isAdmin(): bool { return static::role() === 'admin'; }

    public static function user(): ?array
    {
        if (!static::check()) return null;
        return DB::find('SELECT * FROM users WHERE id = ?', [static::id()]);
    }

    public static function register(array $data): int|string
    {
        DB::execute(
            'INSERT INTO users (nom, email, password, role, actif, token_verification) VALUES (?,?,?,?,?,?)',
            [
                $data['nom'],
                $data['email'],
                sf_bcrypt($data['password']),
                $data['role'] ?? 'user',
                0,
                bin2hex(random_bytes(32)),
            ]
        );
        return DB::lastId();
    }

    public static function generatePasswordReset(string $email): ?string
    {
        $user = DB::find('SELECT * FROM users WHERE email = ?', [$email]);
        if (!$user) return null;
        $token = bin2hex(random_bytes(32));
        DB::execute('UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?',
            [$token, date('Y-m-d H:i:s', time() + 3600), $email]);
        return $token;
    }

    public static function resetPassword(string $token, string $password): bool
    {
        $user = DB::find("SELECT * FROM users WHERE reset_token = ? AND reset_expires > datetime('now')", [$token]);
        if (!$user) return false;
        DB::execute('UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?',
            [sf_bcrypt($password), $user['id']]);
        return true;
    }

    public static function verifyEmail(string $token): bool
    {
        $user = DB::find('SELECT * FROM users WHERE token_verification = ?', [$token]);
        if (!$user) return false;
        DB::execute('UPDATE users SET actif = 1, token_verification = NULL WHERE id = ?', [$user['id']]);
        return true;
    }
}
