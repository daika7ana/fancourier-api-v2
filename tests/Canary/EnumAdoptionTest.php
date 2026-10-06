<?php

declare(strict_types=1);

namespace Fancourier\Tests\Canary;

use Fancourier\Enums\DeliveryMode;
use Fancourier\Enums\DocumentType;
use Fancourier\Enums\LabelFormat;
use Fancourier\Enums\Language;
use Fancourier\Enums\OrderType;
use Fancourier\Enums\PaymentType;
use Fancourier\Enums\PudoType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Phase 3b W6: the additive `Fancourier\Enums` surface must exist and stay
 * string-backed. tests/Unit/EnumsTest.php covers setter adoption in depth;
 * this is the consumer-facing existence/value contract only.
 */
final class EnumAdoptionTest extends TestCase
{
    #[Test]
    public function every_enum_exists_and_is_a_string_backed_enum(): void
    {
        foreach (self::enums() as $enum) {
            $this->assertTrue(enum_exists($enum), $enum . ' should exist');

            $reflection = new \ReflectionEnum($enum);
            $this->assertTrue($reflection->isBacked(), $enum . ' should be a backed enum');
            $backing = $reflection->getBackingType();
            $this->assertNotNull($backing, $enum . ' should declare a backing type');
            $this->assertSame('string', $backing->getName(), $enum . ' should be string-backed');
        }
    }

    /** @return list<class-string> */
    private static function enums(): array
    {
        return [
            DeliveryMode::class,
            DocumentType::class,
            LabelFormat::class,
            Language::class,
            OrderType::class,
            PaymentType::class,
            PudoType::class,
        ];
    }
}
