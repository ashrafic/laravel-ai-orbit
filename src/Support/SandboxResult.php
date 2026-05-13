<?php

namespace Ashrafic\AiOrbit\Support;

class SandboxResult
{
    public function __construct(
        public readonly string $content,
        public readonly string $mode,
        public readonly ?string $warning = null,
        public readonly ?string $sdkConversationId = null,
        public readonly array $metadata = [],
        public readonly array $toolCalls = [],
        public readonly array $toolResults = [],
    ) {}
}
