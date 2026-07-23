<?php

declare(strict_types=1);

namespace Acme\Signer;

use Acme\Signer\Application\Service\Vault;
use Acme\Signer\Presentation\Http\Panel;

final class HostContainer
{
    public function vault(): Vault
    {
        return new Vault();
    }

    public function panel(): Panel
    {
        return new Panel();
    }
}
