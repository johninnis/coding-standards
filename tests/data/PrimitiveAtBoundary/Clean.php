<?php

declare(strict_types=1);

namespace Acme\App\Application\Service;

use Acme\App\Domain\ValueObject\PublicKey;

final class Verifier
{
    public function verify(PublicKey $publicKey, string $hex, int $id, string $message): bool
    {
        return $hex !== '' && $id > 0 && $message !== '' && $publicKey->hex !== '';
    }
}
