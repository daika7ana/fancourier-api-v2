<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit;

use Fancourier\Auth;
use Fancourier\Enums\DeliveryMode;
use Fancourier\Enums\DocumentType;
use Fancourier\Enums\LabelFormat;
use Fancourier\Enums\Language;
use Fancourier\Enums\OrderType;
use Fancourier\Enums\PaymentType;
use Fancourier\Enums\PudoType;
use Fancourier\Objects\AwbExtern;
use Fancourier\Objects\AwbIntern;
use Fancourier\Request\AbstractRequest;
use Fancourier\Request\CreateCourierOrder;
use Fancourier\Request\GetPudo;
use Fancourier\Request\PrintAwb;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Phase 3b W4: the additive backed enums must mirror the code-list constants
 * (or the documented literal where no constant exists), and passing an enum to
 * a setter must store exactly the string the enum carries.
 */
final class EnumsTest extends TestCase
{
    #[Test]
    public function payment_type_cases_match_the_constants(): void
    {
        $this->assertSame(AbstractRequest::TYPE_SENDER, PaymentType::Expeditor->value);
        $this->assertSame(AbstractRequest::TYPE_RECIPIENT, PaymentType::Destinatar->value);
        $this->assertSame(AbstractRequest::TYPE_OTHER, PaymentType::Altul->value);
    }

    #[Test]
    public function order_type_cases_match_the_constants(): void
    {
        $this->assertSame(AbstractRequest::ORDER_TYPE_STANDARD, OrderType::Standard->value);
        $this->assertSame(AbstractRequest::ORDER_TYPE_EXPRESS_LOCO_1H, OrderType::ExpressLoco1h->value);
        $this->assertSame(AbstractRequest::ORDER_TYPE_EXPRESS_LOCO_2H, OrderType::ExpressLoco2h->value);
        $this->assertSame(AbstractRequest::ORDER_TYPE_EXPRESS_LOCO_4H, OrderType::ExpressLoco4h->value);
        $this->assertSame(AbstractRequest::ORDER_TYPE_EXPRESS_LOCO_6H, OrderType::ExpressLoco6h->value);
    }

    #[Test]
    public function pudo_type_cases_match_the_constants(): void
    {
        $this->assertSame(AbstractRequest::PUDO_FANBOX, PudoType::Fanbox->value);
        $this->assertSame(AbstractRequest::PUDO_PAYPOINT, PudoType::Paypoint->value);
        $this->assertSame(AbstractRequest::PUDO_OFFICE, PudoType::Office->value);
    }

    #[Test]
    public function delivery_mode_cases_match_the_literals(): void
    {
        $this->assertSame('rutier', DeliveryMode::Rutier->value);
        $this->assertSame('aerian', DeliveryMode::Aerian->value);
    }

    #[Test]
    public function document_type_cases_match_the_literals(): void
    {
        $this->assertSame('document', DocumentType::Document->value);
        $this->assertSame('non document', DocumentType::NonDocument->value);
    }

    #[Test]
    public function language_cases_match_the_literals(): void
    {
        $this->assertSame('ro', Language::Ro->value);
        $this->assertSame('en', Language::En->value);
    }

    #[Test]
    public function label_format_cases_match_the_literals(): void
    {
        $this->assertSame('A4', LabelFormat::A4->value);
        $this->assertSame('A5', LabelFormat::A5->value);
        $this->assertSame('A6', LabelFormat::A6->value);
    }

    #[Test]
    public function payment_type_enum_packs_like_its_string(): void
    {
        $fromEnum = (new AwbIntern())->setPaymentType(PaymentType::Expeditor)->setReturnPayment(PaymentType::Destinatar);
        $fromString = (new AwbIntern())->setPaymentType('expeditor')->setReturnPayment('destinatar');

        $this->assertSame('expeditor', $fromEnum->getPaymentType());
        $this->assertSame('destinatar', $fromEnum->getReturnPayment());
        $this->assertSame($fromString->pack(), $fromEnum->pack());
    }

    #[Test]
    public function delivery_mode_enum_packs_like_its_string(): void
    {
        $fromEnum = (new AwbExtern())->setDeliveryMode(DeliveryMode::Aerian);
        $fromString = (new AwbExtern())->setDeliveryMode('aerian');

        $this->assertSame('aerian', $fromEnum->getDeliveryMode());
        $this->assertSame($fromString->pack(), $fromEnum->pack());
    }

    #[Test]
    public function document_type_enum_packs_like_its_string(): void
    {
        $fromEnum = (new AwbExtern())->setDocumentType(DocumentType::NonDocument);
        $fromString = (new AwbExtern())->setDocumentType('non document');

        $this->assertSame('non document', $fromEnum->getDocumentType());
        $this->assertSame($fromString->pack(), $fromEnum->pack());
    }

    #[Test]
    public function order_type_enum_stores_like_its_string(): void
    {
        $fromEnum = (new CreateCourierOrder())->setOrderType(OrderType::ExpressLoco2h);
        $fromString = (new CreateCourierOrder())->setOrderType('Express Loco 2h');

        $this->assertSame('Express Loco 2h', $fromEnum->getOrderType());
        $this->assertSame($fromString->getOrderType(), $fromEnum->getOrderType());
    }

    #[Test]
    public function pudo_type_enum_packs_like_its_string(): void
    {
        $fromEnum = (new GetPudo())->setType(PudoType::Paypoint);
        $fromString = (new GetPudo())->setType('paypoint');

        $this->assertSame('paypoint', $fromEnum->getType());
        $this->assertSame($fromString->pack(), $fromEnum->pack());
    }

    #[Test]
    public function language_enum_packs_like_its_string(): void
    {
        $fromEnum = (new PrintAwb())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'))->setLang(Language::En);
        $fromString = (new PrintAwb())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'))->setLang('en');

        $this->assertSame('en', $fromEnum->getLang());
        $this->assertSame($fromString->pack(), $fromEnum->pack());
    }

    #[Test]
    public function label_format_enum_packs_like_its_string(): void
    {
        $fromEnum = (new PrintAwb())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'))->setSize(LabelFormat::A5);
        $fromString = (new PrintAwb())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'))->setSize('A5');

        $this->assertSame('A5', $fromEnum->getSize());
        $this->assertSame($fromString->pack(), $fromEnum->pack());
    }
}
