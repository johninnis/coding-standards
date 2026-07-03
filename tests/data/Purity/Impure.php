<?php

declare(strict_types=1);

namespace Acme\App\Domain\Service;

final class ImpureCalculator
{
    public function compute(): int
    {
        $now = time();
        $roll = rand();
        $data = file_get_contents('/etc/hosts');
        $clock = new \DateTimeImmutable();
        $id = $_GET['id'];

        return $now + $roll + strlen((string) $data) + (int) $clock->format('U') + (int) $id;
    }
}
