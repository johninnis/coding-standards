<?php

declare(strict_types=1);

// A host/example script in the global namespace wiring up the concrete implementation.
// This is driven-port wiring, not the package owning an internal collaborator.

use Acme\Greetings\Application\Service\Greeter;

$greeter = new Greeter();
$greeter->greet('world');
