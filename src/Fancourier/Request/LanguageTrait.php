<?php

namespace Fancourier\Request;

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

    public function setLanguage(string $language): static
    {
        $language = trim(strtolower($language));
        if (in_array($language, ['ro', 'en'])) {
            $this->language = $language;
        }
        return $this;
    }
}
