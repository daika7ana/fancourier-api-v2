<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Objects;

use Fancourier\Objects\AwbExtern;
use Fancourier\Request\AbstractRequest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AwbExternTest extends TestCase
{
    #[Test]
    public function it_starts_with_expected_defaults(): void
    {
        $awb = new AwbExtern();

        $this->assertSame('Export', $awb->getService());
        $this->assertSame('rutier', $awb->getDeliveryMode());
        $this->assertSame('document', $awb->getDocumentType());
        $this->assertSame('', $awb->getBank());
        $this->assertSame('', $awb->getIban());
        $this->assertSame(0, $awb->getEnvelopes());
        $this->assertSame(0, $awb->getParcels());
        $this->assertSame(0, $awb->getWeight());
        $this->assertSame(['length' => 0, 'height' => 0, 'width' => 0], $awb->getSizes());
        $this->assertSame('', $awb->getReimbursement());
        $this->assertSame('RON', $awb->getCurrency());
        $this->assertSame(0, $awb->getDeclaredValue());
        $this->assertSame('', $awb->getRefund());
        $this->assertSame('', $awb->getReturnPayment());
        $this->assertSame([], $awb->getOptions());
        $this->assertFalse($awb->hasErrors());
        $this->assertSame([], $awb->getErrors());
        $this->assertNull($awb->getAwb());
        // default payment is the shared sender type.
        $this->assertSame(AbstractRequest::TYPE_SENDER, $awb->getPaymentType());
    }

    #[Test]
    public function it_chains_info_setters_and_returns_itself(): void
    {
        $awb = new AwbExtern();

        $result = $awb
            ->setService('Export')
            ->setBank('BCR')
            ->setIban('RO00BCR0000000000000000')
            ->setEnvelopes(1)
            ->setParcels(2)
            ->setWeight(3.5)
            ->setReimbursement(100.0)
            ->setCurrency('EUR')
            ->setDeclaredValue(250.0)
            ->setPaymentType('expeditor')
            ->setRefund('refund')
            ->setReturnPayment('destinatar')
            ->setNotes('fragile')
            ->setContents('books')
            ->setCostCenter('CC1')
            ->setUITCode('UIT1');

        $this->assertSame($awb, $result);
        $this->assertSame('Export', $awb->getService());
        $this->assertSame('BCR', $awb->getBank());
        $this->assertSame('RO00BCR0000000000000000', $awb->getIban());
        $this->assertSame(1, $awb->getEnvelopes());
        $this->assertSame(2, $awb->getParcels());
        $this->assertSame(3.5, $awb->getWeight());
        $this->assertSame(100.0, $awb->getReimbursement());
        $this->assertSame('EUR', $awb->getCurrency());
        $this->assertSame(250.0, $awb->getDeclaredValue());
        $this->assertSame('expeditor', $awb->getPaymentType());
        $this->assertSame('refund', $awb->getRefund());
        $this->assertSame('destinatar', $awb->getReturnPayment());
        $this->assertSame('fragile', $awb->getNotes());
        $this->assertSame('books', $awb->getContents());
        $this->assertSame('CC1', $awb->getCostCenter());
        $this->assertSame('UIT1', $awb->getUITCode());
    }

    #[Test]
    public function it_normalises_delivery_mode_and_document_type(): void
    {
        $awb = new AwbExtern();

        $awb->setDeliveryMode('AERIAN');
        $this->assertSame('aerian', $awb->getDeliveryMode());

        $awb->setDeliveryMode('invalid');
        $this->assertSame('aerian', $awb->getDeliveryMode());

        $awb->setDocumentType('NON DOCUMENT');
        $this->assertSame('non document', $awb->getDocumentType());

        $awb->setDocumentType('invalid');
        $this->assertSame('non document', $awb->getDocumentType());
    }

    #[Test]
    public function it_sets_and_gets_dimensions(): void
    {
        $awb = (new AwbExtern())->setSizes(10, 20, 30);

        $this->assertSame(['length' => 10, 'height' => 20, 'width' => 30], $awb->getSizes());
        $this->assertSame(20, $awb->getHeight());
        $this->assertSame(10, $awb->getLength());
        $this->assertSame(30, $awb->getWidth());

        $awb->setHeight(1)->setLength(2)->setWidth(3);
        $this->assertSame(1, $awb->getHeight());
        $this->assertSame(2, $awb->getLength());
        $this->assertSame(3, $awb->getWidth());
    }

    #[Test]
    public function it_rejects_non_positive_dimensions(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("You can't set sizes to 0 or lower");

        (new AwbExtern())->setSizes(10, 0, 30);
    }

    #[Test]
    public function it_manages_options(): void
    {
        $awb = new AwbExtern();

        $awb->addOption('a');
        $this->assertSame(['A'], $awb->getOptions());

        $awb->addOption('xy');
        $this->assertSame(['A'], $awb->getOptions());

        $this->assertSame($awb, $awb->resetOptions());
        $this->assertSame([], $awb->getOptions());
    }

    #[Test]
    public function it_chains_recipient_setters(): void
    {
        $awb = new AwbExtern();

        $awb->setRecipientName('COM S.R.L.')
            ->setContactPerson('Ion Popescu')
            ->setPhone('0720000000')
            ->setAltPhone('0730000000')
            ->setEmail('email@example.com')
            ->setCountry('Romania')
            ->setCounty('Ialomita')
            ->setCity('Fetesti')
            ->setStreet('Grausor')
            ->setNumber('12A')
            ->setPostalCode('925100')
            ->setBuilding('B1')
            ->setEntrance('A')
            ->setFloor('2')
            ->setApartment('4');

        $this->assertSame('COM S.R.L.', $awb->getRecipientName());
        $this->assertSame('Ion Popescu', $awb->getContactPerson());
        $this->assertSame('0720000000', $awb->getPhone());
        $this->assertSame('0730000000', $awb->getAltPhone());
        $this->assertSame('email@example.com', $awb->getEmail());
        $this->assertSame('Romania', $awb->getCountry());
        $this->assertSame('Ialomita', $awb->getCounty());
        $this->assertSame('Fetesti', $awb->getCity());
        $this->assertSame('Grausor', $awb->getStreet());
        $this->assertSame('12A', $awb->getNumber());
        $this->assertSame('925100', $awb->getPostalCode());
        $this->assertSame('B1', $awb->getBuilding());
        $this->assertSame('A', $awb->getEntrance());
        $this->assertSame('2', $awb->getFloor());
        $this->assertSame('4', $awb->getApartment());
    }

    #[Test]
    public function it_chains_sender_setters(): void
    {
        $awb = new AwbExtern();

        $awb->setSenderName('NETWORK SRL')
            ->setSenderContactPerson('Ioana')
            ->setSenderPhone('0740000000')
            ->setSenderAltPhone('0750000000')
            ->setSenderEmail('sender@example.com')
            ->setSenderCounty('Bucuresti')
            ->setSenderCity('Bucuresti')
            ->setSenderStreet('Glucoza')
            ->setSenderNumber('11C')
            ->setSenderPostalCode('020331')
            ->setSenderBuilding('B2')
            ->setSenderEntrance('C')
            ->setSenderFloor('3')
            ->setSenderApartment('5');

        $this->assertSame('NETWORK SRL', $awb->getSenderName());
        $this->assertSame('Ioana', $awb->getSenderContactPerson());
        $this->assertSame('0740000000', $awb->getSenderPhone());
        $this->assertSame('0750000000', $awb->getSenderAltPhone());
        $this->assertSame('sender@example.com', $awb->getSenderEmail());
        $this->assertSame('Bucuresti', $awb->getSenderCounty());
        $this->assertSame('Bucuresti', $awb->getSenderCity());
        $this->assertSame('Glucoza', $awb->getSenderStreet());
        $this->assertSame('11C', $awb->getSenderNumber());
        $this->assertSame('020331', $awb->getSenderPostalCode());
        $this->assertSame('B2', $awb->getSenderBuilding());
        $this->assertSame('C', $awb->getSenderEntrance());
        $this->assertSame('3', $awb->getSenderFloor());
        $this->assertSame('5', $awb->getSenderApartment());
    }

    #[Test]
    public function it_packs_the_default_payload(): void
    {
        $packed = (new AwbExtern())->pack();

        $this->assertSame('rutier', $packed['info']['deliveryMode']);
        $this->assertSame('Export', $packed['info']['service']);
        $this->assertSame('document', $packed['info']['contentType']);
        $this->assertSame(['parcel' => 0, 'envelope' => 0], $packed['info']['packages']);
        $this->assertSame(['length' => 0, 'height' => 0, 'width' => 0], $packed['info']['dimensions']);
        $this->assertSame('', $packed['info']['cod']);
        $this->assertSame('RON', $packed['info']['currency']);
        $this->assertSame([], $packed['info']['options']);
        $this->assertSame([
            'country' => '',
            'region' => '',
            'locality' => '',
            'street' => '',
            'streetNo' => '',
            'zipCode' => '',
            'building' => '',
            'entrance' => '',
            'floor' => '',
            'apartment' => '',
        ], $packed['recipient']['address']);
    }

    #[Test]
    public function it_packs_the_populated_payload(): void
    {
        $awb = new AwbExtern();
        $awb->setSenderName('NETWORK SRL')
            ->setSenderContactPerson('Ioana')
            ->setSenderNumber('11C')
            ->setRecipientName('COM S.R.L.')
            ->setCountry('Romania')
            ->setCounty('Ialomita')
            ->setCity('Fetesti')
            ->setStreet('Grausor')
            ->setNumber('12A')
            ->setPostalCode('925100')
            ->setWeight(3.5)
            ->setDeclaredValue(250.0)
            ->setReimbursement(100.0)
            ->setCurrency('EUR');

        $packed = $awb->pack();

        $this->assertSame('NETWORK SRL', $packed['sender']['name']);
        $this->assertSame('Ioana', $packed['sender']['contactPerson']);
        $this->assertSame('11C', $packed['sender']['address']['streetNo']);
        $this->assertSame('COM S.R.L.', $packed['recipient']['name']);
        $this->assertSame('Romania', $packed['recipient']['address']['country']);
        $this->assertSame('Ialomita', $packed['recipient']['address']['region']);
        $this->assertSame('925100', $packed['recipient']['address']['zipCode']);
        $this->assertSame(3.5, $packed['info']['weight']);
        $this->assertSame(250.0, $packed['info']['declaredValue']);
        $this->assertSame(100.0, $packed['info']['cod']);
        // currency is now emitted when set.
        $this->assertSame('EUR', $packed['info']['currency']);
    }

    #[Test]
    public function it_parses_a_successful_result(): void
    {
        $awb = new AwbExtern();
        $awb->setResult(['awbNumber' => '2347300120337']);

        $this->assertFalse($awb->hasErrors());
        $this->assertSame([], $awb->getErrors());
        $this->assertSame('2347300120337', $awb->getAwb());
    }

    #[Test]
    public function it_parses_an_error_result(): void
    {
        $awb = new AwbExtern();
        $awb->setResult([
            'awbNumber' => '',
            'errors' => ['The selected service is invalid'],
        ]);

        $this->assertTrue($awb->hasErrors());
        $this->assertSame(['The selected service is invalid'], $awb->getErrors());
        $this->assertSame('', $awb->getAwb());
    }
}
