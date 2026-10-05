<?php

namespace Fancourier\Request;

use Fancourier\Response\CreateCourierOrder as CreateCourierOrderResponse;

/**
 * Class CreateCourierOrder
 * @package Fancourier\Request
 * @SuppressWarnings(PHPMD)
 */
class CreateCourierOrder extends AbstractRequest implements RequestInterface
{
	protected string $gateway = 'order';
	protected string $method = 'POST';
	
	protected string $awbNumber = '';
	protected int $parcels = 0;
	protected int $envelopes = 0;
	
	protected int|float $weight = 1; // kg
	protected int|float $width = 0; // cm
	protected int|float $length = 0; // cm
	protected int|float $height = 0; // cm
	
	protected string $orderType = 'Standard';
	
	protected string $pickupDate = ''; // YYYY-mm-dd
	/** @var array{min?: int|string, max?: int|string} */
	protected array $pickupHours = []; // ['min', 'max'] => pickupHours.first, pickupHours.second
	
	protected string $notes = '';										// info.observations
	
    protected ?string $name = null;										// info.recipient.name
    protected string $contactPerson = '';								// info.recipient.contactPerson
    protected string $phone = '';										// info.recipient.phone
    protected string $altPhone = '';									// info.recipient.secondaryPhone
    protected string $email = '';									// info.recipient.email
	
    protected string $county = ''; 									// info.recipient.address.county
    protected string $city = ''; // locality							// info.recipient.address.locality
    protected string $street = '';										// info.recipient.address.street
    protected string $number = '';									// info.recipient.address.streetNo
	
    protected string $postalCode = '';								// info.recipient.address.zipcode
	
    protected string $building = '';								// info.recipient.address.building
    protected string $entrance = '';								// info.recipient.address.entrance
    protected string $floor = '';									// info.recipient.address.floor
    protected string $apartment = '';								// info.recipient.address.apartment
	
	
	
    public function __construct()
    {
        parent::__construct();
        $this->response = new CreateCourierOrderResponse();
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function pack(): array
    {
		$arr = [
				"clientId" => $this->auth->getClientId(), //obligatoriu 
				"info" => [
							'awbNumber'	=> $this->awbNumber,
							'packages'	=> [
											'parcel'	=> $this->parcels,
											'envelope'	=> $this->envelopes
											],
							'weight'		=> $this->weight,
							'dimensions'	=> [
												'width' => $this->width,
												'length' => $this->length,
												'height' => $this->height
												],
							'orderType'	=> $this->orderType,
							'pickupDate'	=> $this->pickupDate,
							'pickupHours'	=> [
												'first' => $this->pickupHours['min'],
												'second' => $this->pickupHours['max'],
												],
							'observations' => $this->notes,
							]
			];
			
			if (strtolower($this->orderType) != 'standard')
				{
				// doar pt orderType = 'Express Loco ...'
				$arr["recipient"] = [ //obligatoriu
						"name" => $this->name, 
						"contactPerson" => $this->contactPerson, // obligatoriu
						"phone" => $this->phone,
						"secondaryPhone" => $this->altPhone, // optional
						"email" => $this->email, 
						"address" => [ //obligatoriu
								"county" => $this->county, // {{url}}/counties 
								"locality" => $this->city, // {{url}}/localities 
								"street" => $this->street, // {{url}}/streets 
								"streetNo" => strval($this->number), 
								"zipCode" => $this->postalCode,
								"building" => $this->building, 
								"entrance" => $this->entrance, 
								"floor" => $this->floor, 
								"apartment" => $this->apartment,
								//"country" => "Romania" // optional
								] 
						];
				}
		
		return $arr;
	
    }
	
	/**
	 * @return string
	 */
	public function getAwb(): string
	{
		return $this->awbNumber;
	}
	
	public function setAwb(string $awbNo): static
	{
		$this->awbNumber = $awbNo;
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
     * @return int|float (kg)
     */
    public function getWeight(): int|float
    {
        return $this->weight;
    }

    /**
     * @param int|float $weight (in kg)
     * @return static
     */
    public function setWeight(int|float $weight): static
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @return array{length: int|float, height: int|float, width: int|float}
     */
    public function getSizes(): array
    {
        return [
			'length' => $this->length,
			'height' => $this->height,
			'width' => $this->width,
			];
    }

    /**
     * @param int|float $length_cm
     * @param int|float $height_cm
     * @param int|float $width_cm
     * @return static
     */
    public function setSizes(int|float $length_cm, int|float $height_cm, int|float $width_cm): static
    {
		if ( ($length_cm > 0) && ($height_cm > 0) && ($width_cm > 0) )
			{
			$this->length = $length_cm;
			$this->height = $height_cm;
			$this->width = $width_cm;
			
			return $this;
			}
		
		throw new \Exception("You can't set sizes to 0 or lower");
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
     * @return string
     */
    public function getOrderType(): string
    {
        return $this->orderType;
    }

    /**
     * @param string $orderType
     * @return static
     */
    public function setOrderType(string $orderType): static
    {
        $this->orderType = $orderType;
        return $this;
    }

     /**
     * @return string
     */
    public function getPickupDate(): string
    {
        return $this->pickupDate;
    }

    /**
     * @param string $date
     * @return static
     */
    public function setPickupDate(string $date): static
    {
        $this->pickupDate = $date;
        return $this;
    }

     /**
     * @return array{min?: int|string, max?: int|string}
     */
    public function getPickupHours(): array
    {
        return $this->pickupHours;
    }

    /**
     * @param int|string $firstHour
     * @param int|string $lastHour
     * @return static
     */
    public function setPickupHours(int|string $firstHour, int|string $lastHour): static
    {
        $this->pickupHours = [
							'min' => $firstHour,
							'max' => $lastHour
							];
        return $this;
    }

     /**
     * @return string
     */
    public function getNotes(): string
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     * @return static
     */
    public function setNotes(string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

	/**
     * @return string|null
     */
    public function getRecipientName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $recipient
     * @return static
     */
    public function setRecipientName(string $recipient): static
    {
        $this->name = $recipient;
        return $this;
    }

   /**
     * @return string
     */
    public function getContactPerson(): string
    {
        return $this->contactPerson;
    }

    /**
     * @param string $contactPerson
     * @return static
     */
    public function setContactPerson(string $contactPerson): static
    {
        $this->contactPerson = $contactPerson;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return static
     */
    public function setPhone(string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }


    /**
     * @return string
     */
    public function getAltPhone(): string
    {
        return $this->altPhone;
    }

    /**
     * @param string $phone
     * @return static
     */
    public function setAltPhone(string $phone): static
    {
        $this->altPhone = $phone;
        return $this;
    }


    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return static
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getCounty(): string
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
     * @return string
     */
    public function getCity(): string
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
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @param string $street
     * @return static
     */
    public function setStreet(string $street): static
    {
        $this->street = $street;
        return $this;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @param string $number
     * @return static
     */
    public function setNumber(string $number): static
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     * @return static
     */
    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getBuilding(): string
    {
        return $this->building;
    }

    /**
     * @param string $building
     * @return static
     */
    public function setBuilding(string $building): static
    {
        $this->building = $building;
        return $this;
    }

    /**
     * @return string
     */
    public function getEntrance(): string
    {
        return $this->entrance;
    }

    /**
     * @param string $entrance
     * @return static
     */
    public function setEntrance(string $entrance): static
    {
        $this->entrance = $entrance;
        return $this;
    }

    /**
     * @return string
     */
    public function getFloor(): string
    {
        return $this->floor;
    }

    /**
     * @param string $floor
     * @return static
     */
    public function setFloor(string $floor): static
    {
        $this->floor = $floor;
        return $this;
    }

    /**
     * @return string
     */
    public function getApartment(): string
    {
        return $this->apartment;
    }

    /**
     * @param string $apartment
     * @return static
     */
    public function setApartment(string $apartment): static
    {
        $this->apartment = $apartment;
        return $this;
    }
	


}
