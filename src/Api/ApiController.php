<?php
namespace App\Api;

use App\Http\Request;
use App\Http\Response;

abstract class ApiController
{
    protected function json(mixed $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function success(mixed $data = null, string $message = 'OK', int $status = 200): Response
    {
        return $this->json(['success' => true, 'message' => $message, 'data' => $data], $status);
    }

    protected function error(string $message, int $status = 400, mixed $errors = null): Response
    {
        $body = ['success' => false, 'message' => $message];
        if ($errors) $body['errors'] = $errors;
        return $this->json($body, $status);
    }

    protected function created(mixed $data, string $message = 'Créé avec succès'): Response
    {
        return $this->success($data, $message, 201);
    }

    protected function noContent(): Response
    {
        return new Response('', 204);
    }

    protected function notFound(string $message = 'Ressource introuvable'): Response
    {
        return $this->error($message, 404);
    }

    protected function unauthorized(string $message = 'Non autorisé'): Response
    {
        return $this->error($message, 401);
    }

    protected function validate(Request $request, array $rules): array
    {
        $v = \App\Http\Validation\Validator::make($request->all(), $rules);
        return $v->fails() ? $v->errors() : [];
    }

    protected function paginate(string $sql, array $params, int $perPage = 15): Response
    {
        $page  = (int)($_GET['page'] ?? 1);
        $total = \App\Database\DB::find("SELECT COUNT(*) as n FROM ({$sql}) sub", $params)['n'] ?? 0;
        $items = \App\Database\DB::query("{$sql} LIMIT {$perPage} OFFSET " . (($page - 1) * $perPage), $params);

        return $this->json([
            'data'  => $items,
            'meta'  => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => (int)$total,
                'last_page'    => (int)ceil($total / $perPage),
            ],
        ]);
    }
}
