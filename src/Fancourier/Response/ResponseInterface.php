<?php

declare(strict_types=1);

namespace Fancourier\Response;

interface ResponseInterface
{
    public function getErrorCode(): int|string|null;
    public function setErrorCode(int|string|null $errorCode): static;
    public function getErrorMessage(): ?string;
    public function setErrorMessage(?string $errorMessage): static;
    public function getData(): mixed;
    public function setData(mixed $data): static;

    /**
     * HTTP status code of the transfer, or null when unknown (e.g. transport
     * failure before a response was received).
     */
    public function getHttpStatusCode(): ?int;
}
