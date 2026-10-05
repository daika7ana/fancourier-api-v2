<?php

namespace Fancourier\Request;

use Fancourier\Response\GetCourierOrderEvents as GetCourierOrderEventsResponse;

/**
 * Class GetCourierOrderEvents
 * @package Fancourier\Request
 * @SuppressWarnings(PHPMD)
 */
class GetCourierOrderEvents extends AbstractRequest implements RequestInterface
{
	protected string $gateway = 'reports/order-events';
	protected string $method = 'GET';
	
	protected string $language = '';

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
		
		if ($this->language != '')
			{
			$arr['language'] = $this->language;
			}
		
		return $arr;
	
    }
	
    /**
     * @return string
     */
	public function getLanguage(): string
	{
		return $this->language ?? '';
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
