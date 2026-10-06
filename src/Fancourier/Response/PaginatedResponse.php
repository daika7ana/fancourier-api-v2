<?php

declare(strict_types=1);

namespace Fancourier\Response;

/**
 * Shared pagination envelope for responses that expose total/perPage/currentPage.
 *
 * Subclasses parse their own `data` but fill these inherited properties; the
 * computed `totalPages` is `(int) ceil(total / max(1, perPage))`.
 */
class PaginatedResponse extends Generic implements ResponseInterface
{
    protected ?int $total = null;		// total number of entries
    protected ?int $perPage = null;
    protected ?int $currentPage = null;
    protected ?int $totalPages = null;	// total page count (computed)

    #[\Override]
    public function reset(): static
    {
        $this->total = null;
        $this->perPage = null;
        $this->currentPage = null;
        $this->totalPages = null;

        return parent::reset();
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
