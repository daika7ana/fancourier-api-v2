<?php

declare(strict_types=1);

namespace Fancourier\Request;

use Fancourier\Response\GetAwbConfirmations as GetAwbConfirmationsResponse;

/**
 * Class GetAwbConfirmations
 * @package Fancourier\Request
 * @SuppressWarnings(PHPMD)
 */
class GetAwbConfirmations extends AbstractRequest implements RequestInterface
{
    use AwbStringListTrait;

    protected string $gateway = 'reports/get-awb-confirmations';
    protected string $method = 'GET';

    public function __construct()
    {
        parent::__construct();
        $this->response = new GetAwbConfirmationsResponse();
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function pack(): array
    {
        $arr = [
            "clientId" => $this->auth()->getClientId(), //obligatoriu
            "awb" => [], // shipments

        ];

        foreach ($this->awbList as $awb) {
            $arr['awb'][] = $awb;
        }

        return $arr;

    }

}
