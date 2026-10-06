<?php

declare(strict_types=1);

namespace Fancourier\Request;

use Fancourier\Enums\PudoType;
use Fancourier\Response\GetPudo as GetPudoResponse;

class GetPudo extends AbstractRequest implements RequestInterface
{
    protected string $gateway = 'reports/pickup-points';
	protected string $method = 'GET';
	
    protected string $type = self::PUDO_FANBOX;
	protected ?string $pudoId = null;					// if id is set, type will be ignored

    public function __construct()
    {
        parent::__construct();
        $this->response = new GetPudoResponse();
    }

    /** @return array<string, string> */
    #[\Override]
    public function pack(): array
    {
		if (empty($this->pudoId))
			{
			$arr = [
				'type' => $this->type
				];
			}
		else
			{
			$arr = [
				'id' => $this->pudoId
				];
			}
		
		return $arr;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string|PudoType $pudoType
     * @return static
     */
    public function setType(string|PudoType $pudoType): static
    {
        $this->type = $pudoType instanceof PudoType ? $pudoType->value : $pudoType;
        return $this;
    }

    /**
     * @return string|false
     */
    public function getId(): string|false
    {
        return $this->pudoId ?? false;
    }

    /**
     * @param string $pudoId
     * @return static
     */
    public function setId(string $pudoId): static
    {
        $this->pudoId = $pudoId;
        return $this;
    }

}
