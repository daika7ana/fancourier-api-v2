<?php

declare(strict_types=1);

namespace Fancourier\Response;

use Fancourier\Objects\Pudo;

class GetPudo extends Generic implements ResponseInterface
{
    use ResetsResult;

    /** @var array<int|string, Pudo>|null */
    protected ?array $result = null;

    #[\Override]
    public function setData(mixed $datastr): static
    {
        $response_json = json_decode($datastr, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $this->result = [];

            if (isset($response_json['status']) && ($response_json['status'] == 'success')) {
                parent::setData($response_json);

                foreach ($response_json['data'] as $rd) {
                    $this->result[ $rd['id'] ] = new Pudo($rd);
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
     * @return array<int|string, Pudo>
     */
    public function getAll(): array
    {
        return $this->result ?? [];
    }

    /**
     * @param int|string|null $pudoId
     */
    public function get(int|string|null $pudoId = null): Pudo|false
    {
        if ($this->result === null) {
            return false;
        }

        if (is_null($pudoId)) {
            if (count($this->result) == 1) {
                return $this->result[ array_key_first($this->result) ];
            }
        } else {
            if (count($this->result) > 0) {
                return $this->result[ $pudoId ] ?? false;
            }
        }

        return false;
    }

}
