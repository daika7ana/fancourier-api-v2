<?php

declare(strict_types=1);

namespace Fancourier\Response;

class GetAwbConfirmations extends Generic implements ResponseInterface
{
    use ResetsResult;

    /** @var string|null Raw ZIP payload when the API returns a binary body. */
    protected ?string $result = null;

    #[\Override]
    public function setData(mixed $datastr): static
    {
        if (substr($datastr, 0, 2) == 'PK') {
            // we got a ZIP file
            $this->result = $datastr;
            parent::setData($datastr);
        } else {
            $response_json = json_decode($datastr, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                if (isset($response_json['status']) && ($response_json['status'] == 'success')) {
                } else {
                    $this->setErrorFromBody($response_json);
                }
            } else {
                $this->setErrorFromBody($datastr);
            }
        }

        return $this;
    }

    public function getRAWbytes(): ?string
    {
        return $this->result;
    }

    public function getLength(): int
    {
        return strlen(strval($this->result));
    }

    public function saveToFile(string $filename): int|false
    {
        return file_put_contents($filename, $this->result);
    }

}
