<?php

declare(strict_types=1);

namespace Fancourier\Objects;

class Country
{
    protected string $id = '';
    protected string $name = '';
    /** @var array<int, string> */
    protected array $deliveryMode = [];
    protected string $code = '';

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        $this->id = (string) ($data['id'] ?? '');
        $this->name = (string) ($data['name'] ?? '');
        $this->deliveryMode = [];
        // deliveryMode is optional and, when present, must be a list.
        $deliveryModes = $data['deliveryMode'] ?? null;
        if (is_array($deliveryModes)) {
            foreach ($deliveryModes as $dm) {
                if (!is_array($dm)) {
                    continue;
                }
                $this->deliveryMode[ intval($dm['id'] ?? 0) ] = (string) ($dm['name'] ?? '');
            }
        }
        $this->code = (string) ($data['code'] ?? '');
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

    public function hasAirShipping(): bool
    {
        return isset($this->deliveryMode[2]);
    }

    public function hasLandShipping(): bool
    {
        return isset($this->deliveryMode[1]);
    }

    /** @return array<int, string> */
    public function getShipping(): array
    {
        return $this->deliveryMode;
    }
}

/*
    [88] => Array
        (
            [id] => 89
            [name] => Laos
            [deliveryMode] => Array
                (
                    [0] => Array
                        (
                            [id] => 2
                            [name] => Aerian
                        )

                )

            [code] => LA
        )

    [89] => Array
        (
            [id] => 14
            [name] => Letonia
            [deliveryMode] => Array
                (
                    [0] => Array
                        (
                            [id] => 2
                            [name] => Aerian
                        )

                    [1] => Array
                        (
                            [id] => 1
                            [name] => Rutier
                        )

                )

            [code] => LV
        )

    [90] => Array
        (
            [id] => 90
            [name] => Liban
            [deliveryMode] => Array
                (
                    [0] => Array
                        (
                            [id] => 2
                            [name] => Aerian
                        )

                )

            [code] => LB
        )

*/
