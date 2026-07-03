<?php

declare(strict_types=1);

namespace Acme\Logging\Application\Port;

interface LoggerInterface
{
    public function log(string $message): void;
}
