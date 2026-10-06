<?php

declare(strict_types=1);

namespace Fancourier\Objects;

/**
 * Shared AWB result surface: the result fields set by `setResult()` plus their
 * accessors, identical across AwbIntern and AwbExtern.
 */
trait AwbResultTrait
{
    protected ?string $awb = null;

    /** @var array<int, mixed>|null */
    protected ?array $errors = null;

    protected bool $hasErrors = false;

    public function hasErrors(): bool
    {
        return $this->hasErrors;
    }

    /** @return array<int, mixed> */
    public function getErrors(): array
    {
        return $this->errors ?? [];
    }

    public function getAwb(): ?string
    {
        return $this->awb;
    }
}
