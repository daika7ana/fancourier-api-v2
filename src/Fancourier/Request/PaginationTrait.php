<?php

namespace Fancourier\Request;

/**
 * Shared page/perPage state and the `page`/`perPage` payload block.
 *
 * Using classes set their own default `$perPage` in the constructor; the upper
 * bound applied by setPerPage() comes from maxPerPage() so subclasses can widen
 * it without changing the public API.
 */
trait PaginationTrait
{
    protected int $page = 0;
    protected int $perPage = 0;

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): static
    {
        $this->page = $page;
        return $this;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function setPerPage(int $perPage): static
    {
        $max = $this->maxPerPage();
        if ($perPage > $max) {
            $perPage = $max;
        }
        $this->perPage = $perPage;
        return $this;
    }

    /**
     * FAN Courier API page-size ceiling; classes with a higher documented limit
     * override this.
     */
    protected function maxPerPage(): int
    {
        return 100;
    }

    /**
     * Append the pagination keys, preserving first-seen key order.
     *
     * @param array<string, mixed> $arr
     * @return array<string, mixed>
     */
    protected function withPagination(array $arr): array
    {
        if ($this->page > 0) {
            $arr['page'] = $this->page;
        }

        if ($this->perPage > 0) {
            $arr['perPage'] = $this->perPage;
        }

        return $arr;
    }
}
