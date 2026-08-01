<?php

namespace Modules\Glitter\NotificationHub\Support;

class DeliveryMetadataSanitizer
{
    private const SENSITIVE_KEYS = ['authorization', 'proxy-authorization', 'cookie', 'set-cookie', 'password', 'passwd', 'secret', 'token', 'api_key', 'apikey', 'access_token', 'refresh_token', 'client_secret', 'private_key'];

    public function __construct(
        private readonly int $maxDepth = 5,
        private readonly int $maxItems = 100,
        private readonly int $maxScalarLength = 2048,
    ) {}

    /** @param array<string|int, mixed> $metadata @return array<string|int, mixed> */
    public function sanitize(array $metadata): array
    {
        return $this->sanitizeArray($metadata, 0);
    }

    public function sanitizeText(?string $value, int $maxLength = 1000): ?string
    {
        if ($value === null) {
            return null;
        }

        $keys = implode('|', array_map(static fn (string $key): string => preg_quote($key, '/'), self::SENSITIVE_KEYS));
        $value = preg_replace('/-----BEGIN(?: [A-Z0-9]+)* PRIVATE KEY-----.*?-----END(?: [A-Z0-9]+)* PRIVATE KEY-----/is', '[REDACTED]', $value) ?? '';
        $value = preg_replace('/(?im)\b(cookie|set-cookie|authorization|proxy-authorization)\s*:\s*[^\r\n]*/', '$1: [REDACTED]', $value) ?? '';
        $value = preg_replace('/([\"\'])(?:'.$keys.')\1\s*(?::|=>)\s*([\"\'])(?:\\.|(?!\2).)*\2/i', '$1[REDACTED]$1: $2[REDACTED]$2', $value) ?? '';
        $value = preg_replace('/\b('.$keys.')\b\s*[=:]\s*(?:"[^"]*"|\'[^\']*\'|[^\s,;]+)/i', '$1=[REDACTED]', $value) ?? '';
        $value = preg_replace('/\b(Bearer|Basic)\s+[A-Za-z0-9+\/=_.-]+/i', '$1 [REDACTED]', $value) ?? '';

        return mb_substr($value, 0, $maxLength);
    }

    private function sanitizeArray(array $items, int $depth): array
    {
        if ($depth >= $this->maxDepth) {
            return ['_truncated' => true];
        }

        $result = [];
        foreach (array_slice($items, 0, $this->maxItems, true) as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), self::SENSITIVE_KEYS, true)) {
                $result[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $result[$key] = $this->sanitizeArray($value, $depth + 1);
            } elseif (is_scalar($value) || $value === null) {
                $result[$key] = is_string($value) ? mb_substr($value, 0, $this->maxScalarLength) : $value;
            } else {
                $result[$key] = '[UNSUPPORTED]';
            }
        }

        if (count($items) > $this->maxItems) {
            $result['_truncated'] = true;
        }

        return $result;
    }
}
