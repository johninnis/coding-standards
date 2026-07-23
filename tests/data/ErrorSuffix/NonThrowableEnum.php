<?php

declare(strict_types=1);

namespace Acme\App\Domain\Failure;

enum MetadataError: string
{
    case Missing = 'missing';
    case Malformed = 'malformed';
}
