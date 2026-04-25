<?php
namespace App\Middleware;

use App\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    public function handle(Request $request): ?Response
    {
        if (!session()->isAuthenticated()) {
            session()->flash('error', 'Accès réservé aux administrateurs.');
            return Response::create('', 302, ['Location' => '/login']);
        }
        return null;
    }
}
