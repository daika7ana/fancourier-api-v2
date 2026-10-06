<?php

declare(strict_types=1);

namespace Fancourier\Request;

/**
 * Shared list of AWB numbers as strings: append via addAwb()/setAwb() and clear
 * via resetAwbs(). Object-valued AWB lists (CreateAwb/CreateAwbExternal) keep
 * their own, differently typed, contract.
 */
trait AwbStringListTrait
{
    /** @var array<string> */
    protected array $awbList = [];

    public function addAwb(string $awb): static
    {
        $this->awbList[] = $awb;

        return $this;
    }

    public function setAwb(string $awb): static
    {
        return $this->addAwb($awb);
    }

    public function resetAwbs(): static
    {
        $this->awbList = [];

        return $this;
    }
}
