<?php
namespace App\View;

class TwigEngine
{
    private string $viewPath;

    public function __construct(string $viewPath)
    {
        $this->viewPath = rtrim($viewPath, '/');
    }

    public function render(string $template, array $data = []): string
    {
        // Chercher d'abord le fichier .php (notre fallback natif)
        $phpFile = $this->viewPath . '/' . str_replace('.twig', '.php', $template);

        if (file_exists($phpFile)) {
            return $this->renderPhp($phpFile, $data);
        }

        // Essayer Twig si installé
        if (class_exists(\Twig\Environment::class)) {
            return $this->renderTwig($template, $data);
        }

        throw new \RuntimeException("Vue introuvable : {$template} (cherché : {$phpFile})");
    }

    private function renderPhp(string $filePath, array $data): string
    {
        extract($data);
        ob_start();
        require $filePath;
        return ob_get_clean();
    }

    private function renderTwig(string $template, array $data): string
    {
        static $twig = null;
        if (!$twig) {
            $loader = new \Twig\Loader\FilesystemLoader($this->viewPath);
            $twig   = new \Twig\Environment($loader, [
                'cache'       => false,
                'debug'       => env('APP_DEBUG', 'true') === 'true',
                'auto_reload' => true,
            ]);
            $twig->addGlobal('session', app('session'));
            $twig->addFunction(new \Twig\TwigFunction('csrf_field', 'csrf_field', ['is_safe' => ['html']]));
            $twig->addFunction(new \Twig\TwigFunction('asset', 'asset'));
            $twig->addFunction(new \Twig\TwigFunction('old', 'old'));
        }
        return $twig->render($template, $data);
    }
}
