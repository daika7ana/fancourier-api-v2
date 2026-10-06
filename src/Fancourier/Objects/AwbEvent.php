<?php

declare(strict_types=1);

namespace Fancourier\Objects;

class AwbEvent
{
    protected string $id;
    protected string $name;

    public function __construct(int|string $id, string $name)
    {
        $this->id = (string) $id;
        $this->name = $name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
