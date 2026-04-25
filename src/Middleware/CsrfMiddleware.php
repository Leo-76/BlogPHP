<?php
namespace App\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Exceptions\HttpException;

class CsrfMiddleware
{
    public function handle(Request $request): ?Response
    {
        if ($request->isPost()) {
            $token = $request->post('_csrf', '');
            if (!hash_equals(csrf_token(), $token)) {
                throw new HttpException(403, 'Token CSRF invalide.');
            }
        }
        return null;
    }
}
