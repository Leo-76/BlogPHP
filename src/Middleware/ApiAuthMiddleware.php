<?php
namespace App\Middleware;

use App\Http\Request;
use App\Http\Response;

class ApiAuthMiddleware
{
    public function handle(Request $request): ?Response
    {
        $token = $request->bearerToken();
        if (!$token) {
            return Response::json(['success' => false, 'message' => 'Token manquant.'], 401);
        }

        $user = \App\Database\DB::find('SELECT * FROM users WHERE api_token = ? AND actif = 1', [$token]);
        if (!$user) {
            return Response::json(['success' => false, 'message' => 'Token invalide.'], 401);
        }

        // Rendre l'utilisateur disponible globalement
        $_REQUEST['_api_user'] = $user;
        return null;
    }
}
