<?php

declare(strict_types=1);

namespace Fancourier\Request;

use Fancourier\Response\GetCounties as GetCountiesResponse;

class GetCounties extends AbstractRequest implements RequestInterface
{
    protected string $gateway = 'reports/counties';
	protected string $method = 'GET';

    public function __construct()
    {
        parent::__construct();
        $this->response = new GetCountiesResponse();
    }

    #[\Override]
    public function pack(): array
    {
		return [];
    }

}
