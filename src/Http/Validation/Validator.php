<?php
namespace App\Http\Validation;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Wrapper autour de symfony/validator
 * Syntaxe compatible avec Laravel/notre ancien Validator
 */
class Validator
{
    private array $errors = [];
    private array $data   = [];

    private function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data = $data;
        $this->run($rules, $messages);
    }

    public static function make(array $data, array $rules, array $messages = []): static
    {
        return new static($data, $rules, $messages);
    }

    private function run(array $rules, array $messages): void
    {
        $validator = Validation::createValidator();

        foreach ($rules as $field => $ruleSet) {
            $value       = $this->data[$field] ?? null;
            $constraints = $this->parseRules(is_array($ruleSet) ? $ruleSet : explode('|', $ruleSet));
            $violations  = $validator->validate($value, $constraints);

            foreach ($violations as $v) {
                $customKey = "{$field}." . $this->getRuleName($v->getConstraint());
                $this->errors[$field][] = $messages[$customKey] ?? $v->getMessage();
            }
        }
    }

    /**
     * Convertit nos règles textuelles en contraintes Symfony
     * Syntaxe identique à Laravel : 'required|email|min:8|unique:users,email'
     */
    private function parseRules(array $rules): array
    {
        $constraints = [];

        foreach ($rules as $rule) {
            [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);

            $constraint = match($name) {
                'required'  => new Assert\NotBlank(message: 'Ce champ est obligatoire.'),
                'email'     => new Assert\Email(message: 'Email invalide.'),
                'min'       => new Assert\Length(min: (int)$param, minMessage: "Minimum {{ limit }} caractères."),
                'max'       => new Assert\Length(max: (int)$param, maxMessage: "Maximum {{ limit }} caractères."),
                'min_val'   => new Assert\GreaterThanOrEqual((float)$param),
                'max_val'   => new Assert\LessThanOrEqual((float)$param),
                'integer'   => new Assert\Type('integer'),
                'numeric'   => new Assert\Type('numeric'),
                'url'       => new Assert\Url(),
                'ip'        => new Assert\Ip(),
                'uuid'      => new Assert\Uuid(),
                'date'      => new Assert\Date(),
                'datetime'  => new Assert\DateTime(),
                'positive'  => new Assert\Positive(),
                'negative'  => new Assert\Negative(),
                'alpha'     => new Assert\Regex('/^[a-zA-ZÀ-ÿ]+$/'),
                'alphanum'  => new Assert\Regex('/^[a-zA-Z0-9]+$/'),
                'confirmed' => new Assert\EqualTo(
                    value: $this->data[$param ?? ''] ?? null,
                    message: 'Les champs ne correspondent pas.'
                ),
                'in'        => new Assert\Choice(explode(',', $param ?? '')),
                'not_in'    => new Assert\NotEqualTo($param),
                'regex'     => new Assert\Regex($param ?? '//'),
                'unique'    => $this->uniqueConstraint($param),
                'image'     => new Assert\Image(maxSize: '5M'),
                'file'      => new Assert\File(maxSize: '10M'),
                'between'   => $this->betweenConstraint($param),
                default     => null,
            };

            if ($constraint) $constraints[] = $constraint;
        }

        return $constraints;
    }

    private function uniqueConstraint(?string $param): Assert\Callback
    {
        [$table, $col] = array_pad(explode(',', $param ?? ''), 2, null);
        $value = $this->data[$col ?? ''] ?? null;

        return new Assert\Callback(function($v, $context) use ($table, $col, $value) {
            if (!$table) return;
            $col = $col ?? 'id';
            $exists = db()->fetchOne("SELECT COUNT(*) FROM {$table} WHERE {$col} = ?", [$v]);
            if ($exists > 0) {
                $context->buildViolation('Cette valeur est déjà utilisée.')->addViolation();
            }
        });
    }

    private function betweenConstraint(?string $param): Assert\Range
    {
        [$min, $max] = array_pad(explode(',', $param ?? ''), 2, null);
        return new Assert\Range(min: (float)$min, max: (float)$max);
    }

    private function getRuleName(object $constraint): string
    {
        $class = get_class($constraint);
        return strtolower(substr($class, strrpos($class, '\\') + 1));
    }

    public function fails(): bool   { return !empty($this->errors); }
    public function passes(): bool  { return empty($this->errors); }
    public function errors(): array { return $this->errors; }
    public function first(string $field): ?string { return $this->errors[$field][0] ?? null; }
}
