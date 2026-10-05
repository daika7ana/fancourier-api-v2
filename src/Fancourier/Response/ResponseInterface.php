<?php
namespace Fancourier\Response;

interface ResponseInterface
{
    public function getErrorCode(): int|string|null;
    public function setErrorCode(int|string|null $errorCode): static;
    public function getErrorMessage(): ?string;
    public function setErrorMessage(?string $errorMessage): static;
    public function getData(): mixed;
    public function setData(mixed $data): static;
}
