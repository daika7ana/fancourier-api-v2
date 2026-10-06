<?php

declare(strict_types=1);

namespace Fancourier\Response;

class CreateCourierOrder extends Generic implements ResponseInterface
{
    /** @var array<int, mixed>|null Decoded payload, assigned for parity; no typed getter reads it. */
    protected ?array $result = null;

    #[\Override]
    public function setData(mixed $datastr): static
    {
        $response_json = json_decode($datastr, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $this->result = [];

            if (isset($response_json['status']) && ($response_json['status'] == 'success')) {
                parent::setData($response_json['data']['id'] ?? null);
            } else {
                $this->setErrorFromBody($response_json);
            }
        } else {
            $this->setErrorFromBody($datastr);
        }


        return $this;
    }

    public function getId(): string|int|null
    {
        $id = $this->getData();

        return is_string($id) || is_int($id) ? $id : null;
    }

}
