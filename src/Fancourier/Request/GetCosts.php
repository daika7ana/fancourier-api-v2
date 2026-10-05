<?php

namespace Fancourier\Request;

use Fancourier\Response\GetCosts as GetCostsResponse;

class GetCosts extends AbstractRequest implements RequestInterface
{
    protected string $gateway = 'reports/awb/internal-tariff';
	protected string $method = 'GET';

    private string $paymentType = self::TYPE_RECIPIENT;	// info['payment']
    private ?string $city = null;
    private ?string $county = null;
    private ?string $senderCity = null;
    private ?string $senderCounty = null;
    private int $envelopes = 0;
    private int $parcels = 0;
    private int|float|null $weight = null;
    private int|float $length = 0;
    private int|float $width = 0;
    private int|float $height = 0;
    private int|float|null $declaredValue = null;
    /** @var array<string> */
    protected array $options = [];	// optional					// info.options
    private string $service = 'Standard';

    public function __construct()
    {
        parent::__construct();
        $this->response = new GetCostsResponse();
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function pack(): array
    {
        $arr = [
			'clientId'	=> $this->auth->getClientId(),
			'info'		=> [
							'service'	=>	$this->service,
							'payment'	=>	$this->paymentType,
							'weight'	=>	$this->weight,
							'packages'	=>	[],
						],
			'recipient'	=> [
						'locality'	=> $this->city,
						'county'	=> $this->county
						],
			];

		if (count($this->options) > 0)
			{
			$arr['info']['options'] = $this->options;
			}

		if ( ($this->width > 0) && ($this->height > 0) && ($this->length > 0) )
			{
			$arr['info']['dimensions'] = [
										'height'	=> $this->height,
										'width'		=> $this->width,
										'length'	=> $this->length,
										];
			}

		if ($this->envelopes > 0)
			{
			$arr['info']['packages']['envelope'] = $this->envelopes;
			}

		if ($this->parcels > 0)
			{
			$arr['info']['packages']['parcel'] = $this->parcels;
			}

		if (!empty($this->declaredValue))
			{
			$arr['info']['declaredValue'] = $this->declaredValue;
			}

		if (!empty($this->senderCity) || !empty($this->senderCounty))
			{
			$arr['sender'] = [];
			}
		if (!empty($this->senderCity))
			{
			$arr['sender']['locality'] = $this->senderCity;
			}
		if (!empty($this->senderCounty))
			{
			$arr['sender']['county'] = $this->senderCounty;
			}

		return $arr;
    }

    /**
     * @return string
     */
    public function getPaymentType(): string
    {
        return $this->paymentType;
    }

    /**
     * @param string $paymentType
     * @return static
     */
    public function setPaymentType(string $paymentType): static
    {
        if ($paymentType != self::TYPE_RECIPIENT && $paymentType != self::TYPE_SENDER) {
            throw new \InvalidArgumentException("Invalid paymentType value");
        }

        $this->paymentType = $paymentType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return static
     */
    public function setCity(string $city): static
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCounty(): ?string
    {
        return $this->county;
    }

    /**
     * @param string $county
     * @return static
     */
    public function setCounty(string $county): static
    {
        $this->county = $county;
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
     * @return int|float|null
     */
    public function getDeclaredValue(): int|float|null
    {
        return $this->declaredValue;
    }

    /**
     * @param int|float $declaredValue
     * @return static
     */
    public function setDeclaredValue(int|float $declaredValue): static
    {
        $this->declaredValue = $declaredValue;
        return $this;
    }

    /**
     * @return array<string>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
	 * Replace all options with string containing options
     * @param string $options
     * @return static
     */
    public function setOptions(string $options): static
    {
        $this->options = str_split($options);
        return $this;
    }

    /**
	 * Add a single option letter
     * @param string $option
     * @return static
     */
    public function addOption(string $option): static
    {
		if (strlen ($option) == 1)
			{
			$this->options[] = strtoupper($option);
			}
        return $this;
    }

    /**
	 * Clear all set options
     * @return static
     */
    public function resetOptions(): static
    {
        $this->options = [];
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
