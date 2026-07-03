<?php

declare(strict_types=1);

namespace Acme\Vo\Domain\ValueObject;

final readonly class Money
{
    public function __construct(
        public int $cents,
        public string $currency,
    ) {
    }
}

final class Label
{
    public string $code;

    public function __construct(string $code)
    {
        $this->code = strtoupper($code);
    }
}
