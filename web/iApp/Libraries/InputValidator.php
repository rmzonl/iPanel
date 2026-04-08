<?php namespace Project\Libraries;

/**
 * Girdi doğrulama ve temizleme yardımcısı
 */
class InputValidator
{
    private array $errors = [];
    private array $data   = [];

    public function __construct(array $rawData)
    {
        $this->data = $rawData;
    }

    public static function from(array $rawData): static
    {
        return new static($rawData);
    }

    /** Zorunlu alan */
    public function required(string $field, string $label): static
    {
        if (empty(trim((string)($this->data[$field] ?? '')))) {
            $this->errors[$field] = "$label alanı zorunludur.";
        }
        return $this;
    }

    /** Geçerli e-posta */
    public function email(string $field, string $label = 'E-posta'): static
    {
        $val = $this->data[$field] ?? '';
        if ($val !== '' && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "$label geçerli bir e-posta adresi olmalıdır.";
        }
        return $this;
    }

    /** Maksimum uzunluk */
    public function maxLength(string $field, int $max, string $label): static
    {
        if (mb_strlen((string)($this->data[$field] ?? '')) > $max) {
            $this->errors[$field] = "$label en fazla $max karakter olabilir.";
        }
        return $this;
    }

    /** Sayısal değer */
    public function numeric(string $field, string $label): static
    {
        $val = $this->data[$field] ?? '';
        if ($val !== '' && !is_numeric($val)) {
            $this->errors[$field] = "$label sayısal olmalıdır.";
        }
        return $this;
    }

    /** İzin verilen değerler */
    public function in(string $field, array $allowed, string $label): static
    {
        $val = $this->data[$field] ?? '';
        if ($val !== '' && !in_array($val, $allowed, true)) {
            $this->errors[$field] = "$label geçersiz bir değer içeriyor.";
        }
        return $this;
    }

    /** Doğrulama geçti mi? */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /** İlk hata mesajı */
    public function firstError(): string
    {
        return array_values($this->errors)[0] ?? '';
    }

    /** Tüm hatalar */
    public function errors(): array
    {
        return $this->errors;
    }

    /** Temizlenmiş veriyi al */
    public function get(string $field, mixed $default = null): mixed
    {
        return $this->data[$field] ?? $default;
    }

    /** XSS'e karşı HTML entity encoding */
    public static function escape(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /** Tüm string değerleri trim + strip_tags */
    public static function sanitize(array $data): array
    {
        array_walk_recursive($data, function (&$v) {
            if (is_string($v)) {
                $v = trim(strip_tags($v));
            }
        });
        return $data;
    }
}
