<?php

declare(strict_types=1);

namespace Acme\App\Infrastructure\Registry;

final class Config
{
    private static ?self $instance = null;

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }
}
