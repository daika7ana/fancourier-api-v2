<?php

declare(strict_types=1);

namespace Fancourier\Objects;

class ServiceOption
{
    protected string $code;
    protected string $name;

    public function __construct(int|string $code, string $name)
    {
        $this->code = (string) $code;
        $this->name = $name;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
