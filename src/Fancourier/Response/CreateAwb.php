<?php

declare(strict_types=1);

namespace Fancourier\Response;

use Fancourier\Objects\AwbIntern;

class CreateAwb extends Generic implements ResponseInterface
{
    /** @var array<int, array<string, mixed>>|null */
    protected ?array $result = null;
    /** @var array<int, AwbIntern> */
    protected array $awbList = [];

    #[\Override]
    public function reset(): static
    {
        $this->result = null;
        $this->awbList = [];

        return parent::reset();
    }

    #[\Override]
    public function setData(mixed $datastr): static
    {
        $response_json = json_decode($datastr, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($response_json)) {
            $this->result = [];

            if (isset($response_json['status']) && ($response_json['status'] !== 'success')) {
                $this->setErrorFromBody($response_json);
            } elseif (isset($response_json['response'])) {
                parent::setData($response_json['response']);
                /*
                {
                "response": [
                        {
                            "awbNumber":2332300120218,
                            "tariff":30.53,
                            "packages":1,
                            "letter":"B",
                            "routingCode":"0200",
                            "office":"Bucuresti",
                            "visualCode":"02-01-01",
                            "errors":null
                        },
                        {
                            "awbNumber":2332300120219,
                            "tariff":32.71,
                            "packages":1,
                            "letter":"B",
                            "routingCode":"0200",
                            "office":"Bucuresti",
                            "visualCode":"02-01-01",
                            "errors":null
                        }
                    ]
                }

                {"response":[
                {
                    "awbNumber":null,
                    "success":false,
                    "errors": {
                            "info.parcels":"At least one envelope or parcel is required"
                            }
                },


                */

                foreach ($response_json['response'] as $result) {
                    $this->result[] = $result;
                }

                // update awbs
                foreach ($this->result as $idx => $result) {
                    $this->awbList[ $idx ]->setResult($result);
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
     * @param array<int, AwbIntern> $awbList
     */
    public function setAwbList(array $awbList): bool
    {
        $this->awbList = $awbList;

        return true;
    }

    /**
     * @return array<int, AwbIntern>
     */
    public function getAll(): array
    {
        if (empty($this->result)) {
            return [];
        }

        return $this->awbList;
    }

}
