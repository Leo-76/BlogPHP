<?php
namespace App\Http;

use Symfony\Component\HttpFoundation\Response as SfResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Étend la vraie Response Symfony
 */
class Response extends SfResponse
{
    public static function make(string $content, int $status = 200, array $headers = []): static
    {
        return new static($content, $status, $headers);
    }

    public static function json(mixed $data, int $status = 200): JsonResponse
    {
        return new JsonResponse($data, $status);
    }

    public static function redirect(string $url, int $status = 302): RedirectResponse
    {
        return new RedirectResponse($url, $status);
    }

    public static function html(string $content, int $status = 200): static
    {
        $response = new static($content, $status);
        $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
        return $response;
    }
}
