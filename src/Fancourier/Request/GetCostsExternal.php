<?php

declare(strict_types=1);

namespace Fancourier\Request;

use Fancourier\Enums\DeliveryMode;
use Fancourier\Response\GetCostsExternal as GetCostsExternalResponse;

class GetCostsExternal extends AbstractRequest implements RequestInterface
{
    protected string $gateway = 'reports/awb/external-tariff';
	protected string $method = 'GET';

    private ?string $senderCity = null;		// sender.locality
    private ?string $senderCounty = null;	// sender.county
    private ?string $country = null;
    private int $envelopes = 0;
    private int $parcels = 0;
    private int|float|null $weight = null;
    private int|float $length = 0;
    private int|float $width = 0;
    private int|float $height = 0;
	
    private string $service = 'Export';
	private string $deliveryMode = 'rutier';		// "rutier" sau "aerian" (metodele disponibile se pot afla prin GetCountries)
	private string $documentType = 'document';		// "document" sau "non document"

    public function __construct()
    {
        parent::__construct();
        $this->response = new GetCostsExternalResponse();
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function pack(): array
    {
        $arr = [
			'clientId'	=> $this->auth()->getClientId(),
			'info'		=> [
							'service'		=>	$this->service,
							'deliveryMode'	=> $this->deliveryMode,
							'documentType'	=> $this->documentType,
							'weight'		=>	$this->weight,
							'dimensions'	=> [
												'height'	=> $this->height,
												'width'		=> $this->width,
												'length'	=> $this->length,
												],
							'packages'		=>	[
												'envelope'	=> $this->envelopes,
												'parcel'	=> $this->parcels,
												],
						],
			'recipient'	=> [
						'country'	=> $this->country,
						],
			];
		
		if ( ($this->senderCity != '') || ($this->senderCounty != '') )
			{
			$arr['sender'] = [];
			
			if ($this->senderCity != '')
				{
				$arr['sender']['locality'] = $this->senderCity;
				}
			
			if ($this->senderCounty != '')
				{
				$arr['sender']['county'] = $this->senderCounty;
				}
			}
		
		return $arr;
    }

    /**
     * @return string
     */
    public function getDeliveryMode(): string
    {
        return $this->deliveryMode;
    }

    /**
     * @param string|DeliveryMode $deliveryMode
     * @return static
     */
    public function setDeliveryMode(string|DeliveryMode $deliveryMode): static
    {
	    $deliveryMode = $deliveryMode instanceof DeliveryMode ? $deliveryMode->value : $deliveryMode;
	    $deliveryMode = strtolower($deliveryMode);
		if ( ($deliveryMode == 'rutier') || ($deliveryMode == 'aerian') )
		{
            $this->deliveryMode = $deliveryMode;
		}
        return $this;
    }

    /**
     * @return string
     */
    public function getDocumentType(): string
    {
        return $this->documentType;
    }

    /**
     * @param string $documentType
     * @return static
     */
    public function setDocumentType(string $documentType): static
    {
	    $documentType = strtolower($documentType);
		if ( ($documentType == 'document') || ($documentType == 'non document') )
		{
            $this->documentType = $documentType;
		}
        return $this;
    }


    /**
     * @return string|null
     */
    public function getSenderCity(): ?string
    {
        return $this->senderCity;
    }

    /**
     * @param string $city
     * @return static
     */
    public function setSenderCity(string $city): static
    {
        $this->senderCity = $city;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSenderCounty(): ?string
    {
        return $this->senderCounty;
    }

    /**
     * @param string $county
     * @return static
     */
    public function setSenderCounty(string $county): static
    {
        $this->senderCounty = $county;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string $country
     * @return static
     */
    public function setCountry(string $country): static
    {
        $this->country = $country;
        return $this;
    }



    /**
     * @return int
     */
    public function getEnvelopes(): int
    {
        return $this->envelopes;
    }

    /**
     * @param int $envelopes
     * @return static
     */
    public function setEnvelopes(int $envelopes): static
    {
        $this->envelopes = $envelopes;
        return $this;
    }

    /**
     * @return int
     */
    public function getParcels(): int
    {
        return $this->parcels;
    }

    /**
     * @param int $parcels
     * @return static
     */
    public function setParcels(int $parcels): static
    {
        $this->parcels = $parcels;
        return $this;
    }

    /**
     * @return int|float|null
     */
    public function getWeight(): int|float|null
    {
        return $this->weight;
    }

    /**
     * @param int|float $weight
     * @return static
     */
    public function setWeight(int|float $weight): static
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @return int|float
     */
    public function getLength(): int|float
    {
        return $this->length;
    }

    /**
     * @param int|float $length
     * @return static
     */
    public function setLength(int|float $length): static
    {
        $this->length = $length;
        return $this;
    }

    /**
     * @return int|float
     */
    public function getWidth(): int|float
    {
        return $this->width;
    }

    /**
     * @param int|float $width
     * @return static
     */
    public function setWidth(int|float $width): static
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return int|float
     */
    public function getHeight(): int|float
    {
        return $this->height;
    }

    /**
     * @param int|float $height
     * @return static
     */
    public function setHeight(int|float $height): static
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return string
     */
    public function getService(): string
    {
        return $this->service;
    }

    /**
     * @param string $service
     * @return static
     */
    public function setService(string $service): static
    {
        $this->service = $service;
        return $this;
    }
}
