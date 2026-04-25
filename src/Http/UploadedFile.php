<?php
namespace App\Http;

class UploadedFile
{
    public function __construct(
        public readonly string $name,
        public readonly string $tmpName,
        public readonly string $mimeType,
        public readonly int    $size,
        public readonly int    $error
    ) {}

    public static function fromArray(array $file): static
    {
        return new static(
            $file['name'], $file['tmp_name'],
            $file['type'], $file['size'], $file['error']
        );
    }

    public function isValid(): bool { return $this->error === UPLOAD_ERR_OK; }

    public function getExtension(): string
    {
        return strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
    }

    public function isImage(): bool
    {
        return in_array($this->getExtension(), ['jpg','jpeg','png','gif','webp','svg']);
    }

    public function getSizeKb(): float { return round($this->size / 1024, 2); }

    public function store(string $directory, ?string $filename = null): string
    {
        if (!$this->isValid()) throw new \RuntimeException("Fichier invalide : erreur {$this->error}");

        $dir = base_path('public/uploads/' . trim($directory, '/'));
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $filename = $filename ?? uniqid() . '_' . time() . '.' . $this->getExtension();
        $path     = $dir . '/' . $filename;

        if (!move_uploaded_file($this->tmpName, $path)) {
            throw new \RuntimeException("Impossible de déplacer le fichier uploadé.");
        }

        return 'uploads/' . trim($directory, '/') . '/' . $filename;
    }

    public function validate(array $rules): array
    {
        $errors = [];
        foreach ($rules as $rule => $value) {
            match($rule) {
                'max_size' => $this->size > $value * 1024 && ($errors[] = "Le fichier ne doit pas dépasser {$value} Ko."),
                'mimes'    => !in_array($this->getExtension(), (array)$value) && ($errors[] = "Extension non autorisée. Autorisées : " . implode(', ', (array)$value)),
                'image'    => !$this->isImage() && ($errors[] = "Le fichier doit être une image."),
                default    => null,
            };
        }
        return $errors;
    }
}
