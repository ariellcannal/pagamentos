<?php

declare(strict_types=1);

namespace CANNALPagamentos\Webhooks;

class WebhookSecurityConfig
{
    /**
     * @param array<string, string> $tokens
     * @param array<string, string> $signatureKeys
     * @param array<string, array<int, string>> $allowIps
     */
    public function __construct(
        private readonly array $tokens = [],
        private readonly array $signatureKeys = [],
        private readonly array $allowIps = [],
        private readonly int $maxTimestampSkew = 300
    ) {
    }

    public function getToken(string $banco): ?string
    {
        return $this->tokens[$banco] ?? null;
    }

    public function getSignatureKey(string $banco): ?string
    {
        return $this->signatureKeys[$banco] ?? null;
    }

    /** @return array<int, string> */
    public function getAllowIps(string $banco): array
    {
        return $this->allowIps[$banco] ?? [];
    }

    public function getMaxTimestampSkew(): int
    {
        return $this->maxTimestampSkew;
    }
}
