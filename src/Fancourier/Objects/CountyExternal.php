<?php

declare(strict_types=1);

namespace Fancourier\Objects;

class CountyExternal
{
    protected string $id;
    protected string $name;
    protected string $code;
    protected string $country;

    public function __construct(int|string $id, string $name, string $code, string $country)
    {
        $this->id = (string) $id;
        $this->name = $name;
        $this->code = $code;
        $this->country = $country;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getCountry(): string
    {
        return $this->country;
    }
}
