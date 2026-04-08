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

    /** Geçerli IP adresi (IPv4 veya IPv6) */
    public function ip(string $field, string $label = 'IP'): static
    {
        $val = $this->data[$field] ?? '';
        if ($val !== '' && !filter_var($val, FILTER_VALIDATE_IP)) {
            $this->errors[$field] = "$label geçerli bir IP adresi olmalıdır.";
        }
        return $this;
    }

    /** CIDR notasyonu dahil geçerli IP/subnet (örn: 192.168.1.0/24) */
    public function ipOrCidr(string $field, string $label = 'IP/CIDR'): static
    {
        $val = $this->data[$field] ?? '';
        if ($val === '') return $this;
        if (str_contains($val, '/')) {
            [$ip, $prefix] = explode('/', $val, 2);
            $validIp = filter_var($ip, FILTER_VALIDATE_IP) !== false;
            $validPrefix = ctype_digit($prefix) && (int)$prefix >= 0 && (int)$prefix <= 128;
            if (!$validIp || !$validPrefix) {
                $this->errors[$field] = "$label geçerli bir IP/CIDR adresi olmalıdır.";
            }
        } elseif (!filter_var($val, FILTER_VALIDATE_IP)) {
            $this->errors[$field] = "$label geçerli bir IP adresi olmalıdır.";
        }
        return $this;
    }

    /** Alan adı (hostname) doğrulama */
    public function domain(string $field, string $label = 'Alan adı'): static
    {
        $val = $this->data[$field] ?? '';
        if ($val !== '' && !preg_match('/^[a-z0-9][a-z0-9.\-]{1,253}[a-z0-9]$/i', $val)) {
            $this->errors[$field] = "$label geçerli bir alan adı olmalıdır.";
        }
        return $this;
    }

    /** Minimum uzunluk */
    public function minLength(string $field, int $min, string $label): static
    {
        if (mb_strlen((string)($this->data[$field] ?? '')) < $min) {
            $this->errors[$field] = "$label en az $min karakter olmalıdır.";
        }
        return $this;
    }

    /** Regex deseniyle eşleşme */
    public function regex(string $field, string $pattern, string $label): static
    {
        $val = $this->data[$field] ?? '';
        if ($val !== '' && !preg_match($pattern, $val)) {
            $this->errors[$field] = "$label geçersiz bir formatta.";
        }
        return $this;
    }

    /** XSS-safe */
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
