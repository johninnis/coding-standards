<?php

declare(strict_types=1);

namespace Acme\Vo\Domain\ValueObject;

final readonly class Tags
{
    public function __construct(public string $csv)
    {
    }

    public function withCsv(string $csv): self
    {
        return new self($csv);
    }

    public function addressLine(): string
    {
        return $this->csv;
    }
}
