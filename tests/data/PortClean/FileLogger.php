<?php

declare(strict_types=1);

namespace Acme\Logging\Application\Service;

use Acme\Logging\Application\Port\LoggerInterface;

final class FileLogger implements LoggerInterface
{
    public function log(string $message): void
    {
    }
}
