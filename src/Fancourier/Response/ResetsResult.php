<?php

declare(strict_types=1);

namespace Fancourier\Response;

/**
 * Clears the parsed `$result` property on reset().
 *
 * For response classes whose only extra state is `$result`; classes with more
 * state keep their own reset().
 */
trait ResetsResult
{
    public function reset(): static
    {
        $this->result = null;

        return parent::reset();
    }
}
