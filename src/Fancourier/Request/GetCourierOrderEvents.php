<?php

declare(strict_types=1);

namespace Fancourier\Request;

use Fancourier\Response\GetCourierOrderEvents as GetCourierOrderEventsResponse;

/**
 * Class GetCourierOrderEvents
 * @package Fancourier\Request
 * @SuppressWarnings(PHPMD)
 */
class GetCourierOrderEvents extends AbstractRequest implements RequestInterface
{
    use LanguageTrait;

    protected string $gateway = 'reports/order-events';
    protected string $method = 'GET';

    public function __construct()
    {
        parent::__construct();
        $this->response = new GetCourierOrderEventsResponse();
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
