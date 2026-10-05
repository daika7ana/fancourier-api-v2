<?php

namespace Fancourier\Response;

use Fancourier\Objects\Street;

class GetStreets extends Generic implements ResponseInterface
{
	/** @var array<int|string, Street>|null */
	protected ?array $result = null;
	protected ?int $total = null;		// total number of street entries
	protected ?int $perPage = null;
	protected ?int $currentPage = null;
	protected ?int $totalPages = null;	// total page count (computed)
	
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
				$this->totalPages = (int) ceil($this->total / max(1, $this->perPage));
				
				foreach ($response_json['data'] as $rd)
					{
					$this->result[ $rd['id'] ] = new Street($rd);
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
	 * @return array<int|string, Street>
	 */
	public function getAll(): array
		{
		return $this->result ?? [];
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
		
	public function getTotalPages(): int
		{
		return $this->totalPages ?? 0;
		}
}
