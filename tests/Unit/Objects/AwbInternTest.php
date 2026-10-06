<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Objects;

use Fancourier\Objects\AwbIntern;
use Fancourier\Request\CreateAwb;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AwbInternTest extends TestCase
{
    #[Test]
    public function it_starts_with_expected_defaults(): void
    {
        $awb = new AwbIntern();

        $this->assertSame('Standard', $awb->getService());
        $this->assertSame('', $awb->getBank());
        $this->assertSame('', $awb->getIban());
        $this->assertSame(0, $awb->getEnvelopes());
        $this->assertSame(0, $awb->getParcels());
        $this->assertSame(0, $awb->getWeight());
        $this->assertSame('', $awb->getReimbursement());
        $this->assertSame('RON', $awb->getCurrency());
        $this->assertSame(0, $awb->getDeclaredValue());
        $this->assertSame(CreateAwb::TYPE_RECIPIENT, $awb->getPaymentType());
        $this->assertSame(CreateAwb::TYPE_SENDER, $awb->getReturnPayment());
        $this->assertSame('', $awb->getCompany());
        $this->assertSame(['length' => 0, 'height' => 0, 'width' => 0], $awb->getSizes());
        $this->assertSame([], $awb->getOptions());
        $this->assertFalse($awb->hasErrors());
        $this->assertSame([], $awb->getErrors());
        $this->assertNull($awb->getAwb());
        $this->assertNull($awb->getDetails());
    }

    #[Test]
    public function it_chains_info_setters_and_returns_itself(): void
    {
        $awb = new AwbIntern();

        $result = $awb
            ->setService('Cont Colector')
            ->setBank('BCR')
            ->setIban('RO00BCR0000000000000000')
            ->setEnvelopes(1)
            ->setParcels(2)
            ->setWeight(3.5)
            ->setReimbursement(100.0)
            ->setCurrency('EUR')
            ->setDeclaredValue(250.0)
            ->setPaymentType(CreateAwb::TYPE_SENDER)
            ->setRefund('refund')
            ->setReturnPayment(CreateAwb::TYPE_RECIPIENT)
            ->setNotes('fragile')
            ->setContents('books')
            ->setCostCenter('CC1')
            ->setUITCode('UIT1');

        $this->assertSame($awb, $result);
        $this->assertSame('Cont Colector', $awb->getService());
        $this->assertSame('BCR', $awb->getBank());
        $this->assertSame('RO00BCR0000000000000000', $awb->getIban());
        $this->assertSame(1, $awb->getEnvelopes());
        $this->assertSame(2, $awb->getParcels());
        $this->assertSame(3.5, $awb->getWeight());
        $this->assertSame(100.0, $awb->getReimbursement());
        $this->assertSame('EUR', $awb->getCurrency());
        $this->assertSame(250.0, $awb->getDeclaredValue());
        $this->assertSame(CreateAwb::TYPE_SENDER, $awb->getPaymentType());
        $this->assertSame('refund', $awb->getRefund());
        $this->assertSame(CreateAwb::TYPE_RECIPIENT, $awb->getReturnPayment());
        $this->assertSame('fragile', $awb->getNotes());
        $this->assertSame('books', $awb->getContents());
        $this->assertSame('CC1', $awb->getCostCenter());
        $this->assertSame('UIT1', $awb->getUITCode());
    }

    #[Test]
    public function it_sets_and_gets_dimensions(): void
    {
        $awb = (new AwbIntern())->setSizes(10, 20, 30);

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

        (new AwbIntern())->setSizes(0, 20, 30);
    }

    #[Test]
    public function it_manages_options(): void
    {
        $awb = (new AwbIntern())->setOptions('ABC');

        $this->assertSame(['A', 'B', 'C'], $awb->getOptions());

        $awb->addOption('d');
        $this->assertSame(['A', 'B', 'C', 'D'], $awb->getOptions());

        $awb->addOption('xy');
        $this->assertSame(['A', 'B', 'C', 'D'], $awb->getOptions());

        $this->assertSame($awb, $awb->resetOptions());
        $this->assertSame([], $awb->getOptions());
    }

    #[Test]
    public function it_chains_recipient_setters(): void
    {
        $awb = new AwbIntern();

        $awb->setRecipientName('COM S.R.L.')
            ->setContactPerson('Ion Popescu')
            ->setPhone('0720000000')
            ->setAltPhone('0730000000')
            ->setEmail('email@example.com')
            ->setCounty('Ialomita')
            ->setCity('Fetesti')
            ->setStreet('Grausor')
            ->setNumber('12A')
            ->setPickupLocation('PUDO1')
            ->setPostalCode('925100')
            ->setBuilding('B1')
            ->setEntrance('A')
            ->setFloor('2')
            ->setApartment('4')
            ->setDropOffLocation('DROP1');

        $this->assertSame('COM S.R.L.', $awb->getRecipientName());
        $this->assertSame('Ion Popescu', $awb->getContactPerson());
        $this->assertSame('0720000000', $awb->getPhone());
        $this->assertSame('0730000000', $awb->getAltPhone());
        $this->assertSame('email@example.com', $awb->getEmail());
        $this->assertSame('Ialomita', $awb->getCounty());
        $this->assertSame('Fetesti', $awb->getCity());
        $this->assertSame('Grausor', $awb->getStreet());
        $this->assertSame('12A', $awb->getNumber());
        $this->assertSame('PUDO1', $awb->getPickupLocation());
        $this->assertSame('925100', $awb->getPostalCode());
        $this->assertSame('B1', $awb->getBuilding());
        $this->assertSame('A', $awb->getEntrance());
        $this->assertSame('2', $awb->getFloor());
        $this->assertSame('4', $awb->getApartment());
        $this->assertSame('DROP1', $awb->getDropOffLocation());
    }

    #[Test]
    public function it_chains_sender_setters(): void
    {
        $awb = new AwbIntern();

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
        $packed = (new AwbIntern())->pack();

        $this->assertSame('Standard', $packed['info']['service']);
        $this->assertSame('', $packed['info']['bank']);
        $this->assertSame('', $packed['info']['bankAccount']);
        $this->assertSame(['parcel' => 0, 'envelope' => 0], $packed['info']['packages']);
        $this->assertSame('', $packed['info']['cod']);
        $this->assertSame('RON', $packed['info']['currency']);
        $this->assertSame('destinatar', $packed['info']['payment']);
        $this->assertSame('expeditor', $packed['info']['returnPayment']);
        $this->assertSame(['length' => 0, 'height' => 0, 'width' => 0], $packed['info']['dimensions']);
        $this->assertSame([], $packed['info']['options']);
        $this->assertSame('', $packed['info']['uitCode']);
        $this->assertSame([
            'name' => '',
            'contactPerson' => '',
            'phone' => '',
            'secondaryPhone' => '',
            'email' => '',
            'address' => [
                'county' => '',
                'locality' => '',
                'street' => '',
                'streetNo' => '',
                'pickupLocationId' => '',
                'zipCode' => '',
                'building' => '',
                'entrance' => '',
                'floor' => '',
                'apartment' => '',
            ],
        ], $packed['recipient']);
        $this->assertSame(['address' => ['dropOffLocationId' => '']], $packed['sender']);
        $this->assertArrayNotHasKey('isValueUnderThreshold', $packed['info']);
    }

    #[Test]
    public function it_packs_the_sender_block_when_sender_is_set(): void
    {
        $awb = new AwbIntern();
        $awb->setSenderName('NETWORK SRL')
            ->setSenderContactPerson('Ioana')
            ->setSenderPhone('0740000000')
            ->setSenderEmail('sender@example.com')
            ->setSenderCounty('Bucuresti')
            ->setSenderCity('Bucuresti')
            ->setSenderStreet('Glucoza')
            ->setSenderNumber('11C')
            ->setSenderPostalCode('020331')
            ->setDropOffLocation('DROP1');

        $sender = $awb->pack()['sender'];

        $this->assertSame('NETWORK SRL', $sender['name']);
        $this->assertSame('Ioana', $sender['contactPerson']);
        $this->assertSame('0740000000', $sender['phone']);
        $this->assertSame('sender@example.com', $sender['email']);
        $this->assertSame('Bucuresti', $sender['address']['county']);
        $this->assertSame('Bucuresti', $sender['address']['locality']);
        $this->assertSame('11C', $sender['address']['streetNo']);
        $this->assertSame('020331', $sender['address']['zipCode']);
        $this->assertSame('DROP1', $sender['address']['dropOffLocationId']);
    }

    #[Test]
    public function it_adds_non_eu_fields_only_when_threshold_is_boolean(): void
    {
        $awb = new AwbIntern();
        $awb->setIsValueUnderThreshold(true)
            ->setCompany('ACME')
            ->setCountryCode('US')
            ->setVatId('VAT123');

        $packed = $awb->pack();

        $this->assertTrue($packed['info']['isValueUnderThreshold']);
        // stored NUE_* values must be emitted, not blanked.
        $this->assertSame('US', $packed['info']['countryCode']);
        $this->assertSame('VAT123', $packed['info']['vatId']);
        $this->assertSame('ACME', $packed['info']['company']);
        $this->assertSame('US', $awb->getCountryCode());
        $this->assertSame('VAT123', $awb->getVatId());
    }

    #[Test]
    public function it_returns_the_threshold_when_it_is_set(): void
    {
        // the guard was inverted and threw when the value was set.
        $awb = (new AwbIntern())->setIsValueUnderThreshold(false);

        $this->assertFalse($awb->getIsValueUnderThreshold());
    }

    #[Test]
    public function it_throws_when_the_threshold_is_not_set(): void
    {
        // guard must throw only when the value is genuinely unset.
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('isValueUnderThreshold is not set!');

        (new AwbIntern())->getIsValueUnderThreshold();
    }

    #[Test]
    public function it_parses_a_successful_result(): void
    {
        $awb = new AwbIntern();
        $awb->setResult([
            'awbNumber' => '2347300120337',
            'success' => true,
            'tariff' => 10.5,
            'vat' => 19,
            'routingCode' => 'BUC',
        ]);

        $this->assertFalse($awb->hasErrors());
        $this->assertSame([], $awb->getErrors());
        $this->assertSame('2347300120337', $awb->getAwb());
        $this->assertSame(10.5, $awb->getDetails()['tariff']);
        $this->assertSame(19, $awb->getDetails()['vat']);
        $this->assertSame('BUC', $awb->getDetails()['routingCode']);
        $this->assertSame('', $awb->getDetails()['letter']);
    }

    #[Test]
    public function it_parses_an_error_result(): void
    {
        $awb = new AwbIntern();
        $awb->setResult([
            'awbNumber' => '',
            'success' => false,
            'errors' => ['The service is invalid'],
        ]);

        $this->assertTrue($awb->hasErrors());
        $this->assertSame(['The service is invalid'], $awb->getErrors());
        $this->assertSame('', $awb->getAwb());
    }
}
