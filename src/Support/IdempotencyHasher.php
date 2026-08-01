<?php

namespace Modules\Glitter\NotificationHub\Support;

class IdempotencyHasher
{
    public function hash(string $extensionType, string $extensionIdentifier, string $definitionType, string $key): string
    {
        return hash('sha256', $this->encode([$extensionType, $extensionIdentifier, $definitionType, $key]));
    }

    /** Unicode bytes are preserved as supplied; normalization belongs at the input boundary. */
    private function encode(array $parts): string
    {
        return implode('', array_map(static fn (string $part): string => strlen($part).':'.$part, $parts));
    }
}
