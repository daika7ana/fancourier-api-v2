<?php

declare(strict_types=1);

namespace Fancourier\Response;

use Fancourier\Objects\CourierOrder;

class GetCourierOrders extends Generic implements ResponseInterface
{
	/** @var array<int|string, CourierOrder>|null */
	protected ?array $result = null;
	protected ?int $total = null;		// total number of pages
	protected ?int $perPage = null;
	protected ?int $currentPage = null;
	
    #[\Override]
    public function setData(mixed $datastr): static
    {
		$response_json = json_decode($datastr, true);
		
		if (json_last_error() === JSON_ERROR_NONE)
			{
			$this->result = [];
			
			if (isset($response_json['status']) && ($response_json['status'] == 'success'))
				{
				parent::setData($response_json);
				
				$this->total = intval($response_json['total']);
				$this->perPage = intval($response_json['perPage']);
				$this->currentPage = intval($response_json['currentPage']);
				
				foreach ($response_json['data'] as $rd)
					{
					$this->result[ $rd['info']['id'] ] = new CourierOrder($rd);
					}

				}
			else
				{
				$this->setErrorFromBody($response_json);
				}
			}
		else
			{
			$this->setErrorFromBody($datastr);
			}


        return $this;
    }
	
	/**
	 * @return array<int|string, CourierOrder>
	 */
	public function getAll(): array
		{
		return $this->result ?? [];
		}
	
	/**
	 * @param int|string $orderId
	 */
	public function get(int|string $orderId): CourierOrder|false
		{
		return $this->result[$orderId] ?? false;
		}
	
	public function getTotal(): int
		{
		return $this->total ?? 0;
		}
		
	public function getPerPage(): int
		{
		return $this->perPage ?? 0;
		}
		
	public function getCurrentPage(): int
		{
		return $this->currentPage ?? 0;
		}
	
	/*
		reports/orders api apparently doesn't follow the same response format as the other api functions as such
		the "total" field contains the total number of pages instead of total entries.
		This function is kept as an alias to the getTotal() function for consistency only
	*/
	public function getTotalPages(): int
		{
		return $this->getTotal();
		}
}
