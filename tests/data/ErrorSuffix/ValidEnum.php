<?php

declare(strict_types=1);

namespace Acme\App\Domain\Failure;

enum MetadataFailure: string
{
    case Missing = 'missing';
    case Malformed = 'malformed';
}
