<?php

declare(strict_types=1);

namespace Fancourier\Response;

use Fancourier\Objects\ShippingSlip;

class GetShippingSlip extends PaginatedResponse
{
    /** @var array<int, ShippingSlip>|null */
    protected ?array $result = null;

    #[\Override]
    public function setData(mixed $datastr): static
    {
        $response_json = json_decode($datastr, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $this->result = [];

            if (isset($response_json['status']) && ($response_json['status'] == 'success')) {
                parent::setData($response_json['data']);

                $this->total = intval($response_json['total']);
                $this->perPage = intval($response_json['perPage']);
                $this->currentPage = intval($response_json['currentPage']);
                $this->totalPages = (int) ceil($this->total / max(1, $this->perPage));

                foreach ($response_json['data'] as $rd) {
                    $this->result[] = new ShippingSlip($rd);
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
     * @return array<int, ShippingSlip>
     */
    public function getAll(): array
    {
        return $this->result ?? [];
    }

    /**
     * @param int $position
     */
    public function get(int $position): ShippingSlip|false
    {
        return $this->result[$position] ?? false;
    }

}
