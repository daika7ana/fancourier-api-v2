<?php

declare(strict_types=1);

namespace Fancourier\Response;

use Fancourier\Objects\CountyExternal;

class GetCountiesExternal extends Generic implements ResponseInterface
{
    /** @var array<int|string, CountyExternal>|null */
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
                    $this->result[ $rd['id'] ] = new CountyExternal($rd['id'], $rd['name'], $rd['code'], $rd['country']);
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
     * @return array<int|string, CountyExternal>
     */
    public function getAll(): array
    {
        return $this->result ?? [];
    }

}
