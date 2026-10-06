<?php

declare(strict_types=1);

namespace Fancourier\Objects;

class CourierOrderTracker
{
	protected string $orderId = '';
	protected string $orderNumber = '';

	protected string $message = '';

	/** @var array<int, array<string, mixed>> */
	protected array $events = [];

	/**
	 * @param array<string, mixed> $data
	 */
	public function __construct(array $data)
		{
		// orderId can be an int in live payloads; the getter returns string.
		$this->orderId = (string) ($data['orderId'] ?? '');
		$this->orderNumber = (string) ($data['orderNumber'] ?? '');

		$this->message = (string) ($data['message'] ?? '');

		$events = $data['events'] ?? null;
		$this->events = is_array($events) ? $events : [];
		}
	
	public function getOrderId(): string
		{
		return $this->orderId;
		}
	
	public function getOrderNo(): string
		{
		return $this->orderNumber;
		}

	public function getMessage(): string
		{
		return $this->message;
		}
	
	/** @return array<int, array<string, mixed>> */
	public function getEvents(): Array
		{
		return $this->events;
		}

	/** @return array<string, mixed> */
	public function getStatus(): array
		{
		if (count($this->events) > 0)
			{
			$last = array_key_last($this->events);
			return $this->events[$last];
			}
		
		return [
				'id'	=>	null,
				'name'	=>	$this->message,
				'date'	=> date("Y-m-d H:i:s")
				];
		}

}
