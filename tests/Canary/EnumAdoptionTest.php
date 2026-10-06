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

    #[Test]
    public function every_enum_exists_and_is_a_string_backed_enum(): void
    {
        foreach (self::enums() as $enum) {
            $this->assertTrue(enum_exists($enum), $enum.' should exist');

            $reflection = new \ReflectionEnum($enum);
            $this->assertTrue($reflection->isBacked(), $enum.' should be a backed enum');
            $backing = $reflection->getBackingType();
            $this->assertNotNull($backing, $enum.' should declare a backing type');
            $this->assertSame('string', $backing->getName(), $enum.' should be string-backed');
        }
    }

    #[Test]
    public function case_values_match_the_documented_literals(): void
    {
        $this->assertSame('expeditor', PaymentType::Expeditor->value);
        $this->assertSame('destinatar', PaymentType::Destinatar->value);
        $this->assertSame('rutier', DeliveryMode::Rutier->value);
        $this->assertSame('aerian', DeliveryMode::Aerian->value);
        $this->assertSame('ro', Language::Ro->value);
        $this->assertSame('A4', LabelFormat::A4->value);
        $this->assertSame('Express Loco 2h', OrderType::ExpressLoco2h->value);
        $this->assertSame('non document', DocumentType::NonDocument->value);
        $this->assertSame('paypoint', PudoType::Paypoint->value);
    }
}
