<?php

declare(strict_types=1);

namespace Acme\App\Presentation\ViewModel;

final readonly class ProfileCard
{
    public function __construct(public string $name, public string $about, public string $picture, public string $banner)
    {
    }

    public static function make(string $name, string $about, string $picture, string $banner): static
    {
        return new static($name, $about, $picture, $banner);
    }
}
