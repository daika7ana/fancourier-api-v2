<?php

declare(strict_types=1);

namespace Fancourier\Response;

use Fancourier\Objects\County;

class GetCounties extends Generic implements ResponseInterface
{
    /** @var array<int|string, County>|null */
    protected ?array $result = null;

    #[\Override]
    public function setData(mixed $datastr): static
    {
        $response_json = json_decode($datastr, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $this->result = [];

            if (isset($response_json['status']) && ($response_json['status'] == 'success')) {
                parent::setData($response_json['data']);

                foreach ($response_json['data'] as $rd) {
                    $this->result[ $rd['name'] ] = new County($rd['id'], $rd['name']);
                }
            } else {
                $this->setErrorFromBody($response_json);
            }
        } else {
            $this->setErrorFromBody($datastr);
        }


        return $this;
    }

    /**
     * @return array<int|string, County>
     */
    public function getAll(): array
    {
        return $this->result ?? [];
    }


    /**
     * @param string $name
     */
    public function getCounty(string $name): County|false
    {
        return $this->result[ $name ] ?? false;
    }
}
