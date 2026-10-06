<?php

declare(strict_types=1);

namespace Fancourier\Response;

class Generic implements ResponseInterface
{
    protected int|string|null $errorCode = null;
    protected ?string $errorMessage = null;
    protected mixed $data = null;

    #[\Override]
    public function getErrorCode(): int|string|null
    {
        return $this->errorCode;
    }

    #[\Override]
    public function setErrorCode(int|string|null $errorCode): static
    {
        $this->errorCode = $errorCode;
        return $this;
    }

    #[\Override]
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    #[\Override]
    public function setErrorMessage(?string $errorMessage): static
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    #[\Override]
    public function getData(): mixed
    {
        return $this->data;
    }

    #[\Override]
    public function setData(mixed $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Record a failure from an API body.
     *
     * Accepts either the decoded body (array) or the raw response string and
     * always leaves a non-empty message and an error code behind, so isOk()
     * cannot report success on a non-success body.
     *
     * @param mixed  $body     Decoded body (array) or raw response string.
     * @param int|string $code Error code to store.
     * @param string $fallback Message used when the body carries none.
     * @return static
     */
    protected function setErrorFromBody(mixed $body = null, int|string $code = -1, string $fallback = 'Unknown error'): static
    {
        $message = null;

        if (is_array($body))
            {
            $message = $body['message'] ?? $body['error'] ?? $body['errors'] ?? null;
            }
        elseif (is_string($body))
            {
            $message = $body;
            }

        if (is_array($message))
            {
            $message = json_encode($message);
            }

        if (!is_string($message) || $message === '')
            {
            $message = $fallback;
            }

        return $this->setErrorMessage($message)->setErrorCode($code);
    }

    public function isOk(): bool
    {
        return empty($this->getErrorCode()) && empty($this->getErrorMessage());
    }
}
