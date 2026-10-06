<?php

declare(strict_types=1);

namespace Fancourier\Request;

use Fancourier\Response\TrackAwb as TrackAwbResponse;

/**
 * Class TrackAwb
 * @package Fancourier\Request
 * @SuppressWarnings(PHPMD)
 */
class TrackAwb extends AbstractRequest implements RequestInterface
{
	use AwbStringListTrait;
	use LanguageTrait;

	protected string $gateway = 'reports/awb/tracking';
	protected string $method = 'GET';

    public function __construct()
    {
        parent::__construct();
        $this->response = new TrackAwbResponse();
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function pack(): array
    {
		$arr = [
				"clientId" => $this->auth()->getClientId(), //obligatoriu 
				"awb" => [] // shipments
				
			];
		
		foreach ($this->awbList as $awb)
			{
			$arr['awb'][] = $awb;
			}
		
		if ($this->language != '')
			{
			$arr['language'] = $this->language;
			}
		
		return $arr;
	
    }


}
