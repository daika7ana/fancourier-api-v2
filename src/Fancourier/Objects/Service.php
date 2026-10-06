<?php

declare(strict_types=1);

namespace Fancourier\Objects;

class Service
{
    protected string $id;
    protected string $name;
    protected string $description;

    public function __construct(int|string $id, string $name, string $description)
    {
        $this->id = (string) $id;
        $this->name = $name;
        $this->description = $description;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
