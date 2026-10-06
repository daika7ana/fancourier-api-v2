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
        $language = $this->getLanguage();
        if ($language != '') {
            $arr['language'] = $language;
        }

        return $arr;
    }

}
