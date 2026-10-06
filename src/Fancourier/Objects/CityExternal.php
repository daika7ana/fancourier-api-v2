<?php

declare(strict_types=1);

namespace Fancourier\Objects;

class CityExternal
{
    protected string $id;
    protected string $name;
    protected string $county;
    protected string $country;

    public function __construct(int|string $id, string $name, string $county, string $country)
    {
        $this->id = (string) $id;
        $this->name = $name;
        $this->county = $county;
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

    public function getCounty(): string
    {
        return $this->county;
    }

    public function getCountry(): string
    {
        return $this->country;
    }
}
