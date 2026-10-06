<?php

declare(strict_types=1);

namespace Fancourier\Request;

use Fancourier\Response\GetAwbEvents as GetAwbEventsResponse;

class GetAwbEvents extends AbstractRequest implements RequestInterface
{
    use LanguageTrait;

    protected string $gateway = 'reports/awb-events';
    protected string $method = 'GET';

    public function __construct()
    {
        parent::__construct();
        $this->response = new GetAwbEventsResponse();
    }

    /** @return array<string, string> */
    #[\Override]
    public function pack(): array
    {
        $arr = [];
        if ($this->language != '') {
            $arr['language'] = $this->language;
        }

        return $arr;
    }

}
