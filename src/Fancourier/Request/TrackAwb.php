<?php

namespace Fancourier\Request;

use Fancourier\Response\TrackAwb as TrackAwbResponse;

/**
 * Class TrackAwb
 * @package Fancourier\Request
 * @SuppressWarnings(PHPMD)
 */
class TrackAwb extends AbstractRequest implements RequestInterface
{
	protected string $gateway = 'reports/awb/tracking';
	protected string $method = 'GET';
	
	/** @var array<string> */
	protected array $awbList = [];
	protected string $language = '';

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
				"clientId" => $this->auth->getClientId(), //obligatoriu 
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
	
	public function addAwb(string $awb): static
	{
		$this->awbList[] = $awb;
		return $this;
	}
	
	public function setAwb(string $awb): static
	{
		return $this->addAwb($awb);
	}
	
	
	public function resetAwbs(): static
	{
		$this->awbList = [];
		return $this;
	}

    /**
     * @return string
     */
	public function getLanguage(): string
	{
		return $this->language;
	}
	
    /**
     * @param string $language
     * @return static
     */
    public function setLanguage(string $language): static
    {
		$language = trim(strtolower($language));
		if (in_array($language, ['ro', 'en']))
			{
			$this->language = $language;
			}
        return $this;
    }


}
