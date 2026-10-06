<?php

declare(strict_types=1);

namespace Fancourier\Request;

use Fancourier\Response\GetServices as GetServicesResponse;

class GetServices extends AbstractRequest implements RequestInterface
{
    protected string $gateway = 'reports/services';
	protected string $method = 'GET';

    public function __construct()
    {
        parent::__construct();
        $this->response = new GetServicesResponse();
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function pack(): array
    {
		return [];
    }
}
