<?php
namespace App\Http;

use App\Database\DB;

abstract class Controller
{
    protected function view(string $template, array $data = []): Response
    {
        // Données globales disponibles dans toutes les vues publiques
        $data['categories'] = DB::query('SELECT * FROM categories ORDER BY nom');
        $data['recents']    = DB::query('SELECT id, titre, slug, created_at FROM articles WHERE publie=1 ORDER BY created_at DESC LIMIT 4');

        $engine = app('view');
        $html   = $engine->render($template, $data);
        return Response::make($html);
    }

    protected function json(mixed $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function redirect(string $url): Response
    {
        return Response::redirect($url);
    }

    protected function back(): Response
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        return Response::redirect($referer);
    }

    protected function validate(Request $request, array $rules): array
    {
        $errors = $request->validate($rules);
        if (!empty($errors)) {
            session()->flash('errors', $errors);
            session()->flash('_old', $request->body);
        }
        return $errors;
    }
}
