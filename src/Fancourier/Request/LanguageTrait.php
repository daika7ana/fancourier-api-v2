<?php

declare(strict_types=1);

namespace Fancourier\Request;

use Fancourier\Enums\Language;

/**
 * Shared `language` request parameter: trimmed, lowercased and restricted to
 * the documented `ro`/`en` allow-list. Unsupported values are ignored (the
 * previous value, or the empty default, is kept).
 */
trait LanguageTrait
{
    protected string $language = '';

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string|Language $language): static
    {
        $language = $language instanceof Language ? $language->value : $language;
        $language = trim(strtolower($language));
        if (in_array($language, ['ro', 'en'])) {
            $this->language = $language;
        }
        return $this;
    }
}
