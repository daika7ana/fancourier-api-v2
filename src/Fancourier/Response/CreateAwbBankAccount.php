<?php

declare(strict_types=1);

namespace Fancourier\Response;

class CreateAwbBankAccount extends Generic implements ResponseInterface
{
    protected ?string $message = null;

    #[\Override]
    public function reset(): static
    {
        $this->message = null;

        return parent::reset();
    }

    #[\Override]
    public function setData(mixed $datastr): static
    {
        $response_json = json_decode($datastr, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->setErrorFromBody($datastr);

            return $this;
        }

        // Native shape: a plain object, no status envelope.
        if (is_array($response_json) && array_key_exists('inserted', $response_json)) {
            $message = $response_json['message'] ?? null;
            parent::setData($response_json['inserted']);
            $this->message = is_string($message) ? $message : null;

            return $this;
        }

        $this->setErrorFromBody($response_json);

        return $this;
    }

    public function getInserted(): ?int
    {
        return is_numeric($this->getData()) ? (int) $this->getData() : null;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
}
