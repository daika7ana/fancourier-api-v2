<?php

declare(strict_types=1);

namespace Fancourier\Request;

use Fancourier\Enums\LabelFormat;
use Fancourier\Enums\Language;
use Fancourier\Response\PrintAwb as PrintAwbResponse;

class PrintAwb extends AbstractRequest implements RequestInterface
{
    protected string $gateway = 'awb/label';
    protected string $method = 'GET';

    /** @var array<string> */
    private array $awbs = [];
    private bool $pdf = true;
    private bool $zpl = false;
    private int $dpi = -1;	// dots per inch. only applies for ZPL
    private string $lang = 'ro';
    private string $size = '';

    public function __construct()
    {
        parent::__construct();
        $this->response = new PrintAwbResponse();
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function pack(): array
    {
        $arr = [
            'clientId' => $this->auth()->getClientId(),
            'awbs' => $this->awbs,
            'language' => $this->lang,
        ];

        // send pdf variable only if active (can't send both pdf and zpl at the same time)
        if ($this->pdf) {
            $arr['pdf'] = 1;
        } elseif // send zpl variable only if active
        ($this->zpl) {
            $arr['zpl'] = 1;
            if ($this->dpi > 0) {
                $arr['dpi'] = $this->dpi;
            }
        }

        // add the format only if user requests a specific size
        if ($this->size != '') {
            $arr['format'] = $this->size;
        }

        return $arr;
    }

    /**
     * @return array<string>
     */
    public function getAwb(): array
    {
        return $this->awbs;
    }

    /**
     * @param string $awb
     * @return static
     */
    public function setAwb(string $awb): static
    {
        return $this->addAwb($awb);
    }

    /**
     * @param string $awb
     * @return static
     */
    public function addAwb(string $awb): static
    {
        $this->awbs[] = $awb;

        return $this;
    }

    /**
     * Returns true if PDF and ZPL are not set (the returned AWB will be in HTML format)
     * @return bool
     */
    public function getHtml(): bool
    {
        return (!$this->pdf && !$this->zpl);
    }

    /**
     * Explicit method to set HTML mode (deactivates PDF / ZPL formats)
     * @param bool $active
     * @return static
     */
    public function setHtml(bool $active = true): static
    {
        // HTML mode means neither PDF nor ZPL is active; turning it off falls back to the default PDF format.
        // ponytail: setHtml(false) always selects PDF; restore the previous format only if a caller needs ZPL back.
        $active = (bool) $active;
        $this->pdf = !$active;
        $this->zpl = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function getPdf(): bool
    {
        return $this->pdf;
    }

    /**
     * @param bool $active
     * @return static
     */
    public function setPdf(bool $active = true): static
    {
        $this->pdf = $active;
        if ($this->zpl) {
            $this->zpl = false;
        }	// disable ZPL in case it's active

        return $this;
    }

    /**
     * @return bool
     */
    public function getZpl(): bool
    {
        return $this->zpl;
    }

    /**
     * @param bool $active
     * @return static
     */
    public function setZpl(bool $active = false): static
    {
        $this->zpl = $active;
        if ($this->pdf) {
            $this->pdf = false;
        }	// disable PDF in case it's active

        return $this;
    }

    /**
     * @return int
     */
    public function getDpi(): int
    {
        return $this->dpi;
    }

    /**
     / Set the DPI (dots per inch) for the returned label (only applies to ZPL)
     * @param int $dpi
     * @return static
     */
    public function setDpi(int $dpi = -1): static
    {
        $this->dpi = $dpi;

        return $this;
    }

    /**
     * @return string
     */
    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * @param string|Language $lang
     * @return static
     */
    public function setLang(string|Language $lang): static
    {
        $lang = $lang instanceof Language ? $lang->value : $lang;
        $lang = strtolower($lang);
        if (!in_array($lang, ['ro', 'en'])) {
            $lang = 'ro';
        }

        $this->lang = $lang;

        return $this;
    }

    /**
     * @return string
     */
    public function getSize(): string
    {
        return $this->size;
    }

    /**
     * @param string|LabelFormat $pageSize - Can be <empty>, 'A4', 'A5' and 'A6' (only for ePOD)
     * @return static
     */
    public function setSize(string|LabelFormat $pageSize = ''): static
    {
        $pageSize = $pageSize instanceof LabelFormat ? $pageSize->value : $pageSize;
        $pageSize = strtoupper($pageSize);
        if (!in_array($pageSize, ['A4', 'A5', 'A6'])) {
            $pageSize = '';
        }

        $this->size = $pageSize;

        return $this;
    }
}
