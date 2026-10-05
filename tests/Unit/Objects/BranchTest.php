<?php

namespace Fancourier\Tests\Unit\Objects;

use Fancourier\Objects\Branch;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BranchTest extends TestCase
{
    private function data(): array
    {
        return [
            'id' => '10',
            'name' => 'Bucuresti',
            'bank' => 'BCR',
            'bankAccount' => 'RO00BCR0000000000000000',
            'email' => 'branch@example.com',
            'phone' => '0210000000',
            'secondaryPhone' => '0210000001',
            'contactPerson' => 'Ion Popescu',
            'address' => [
                'county' => 'Bucuresti',
                'locality' => 'Bucuresti',
                'countyId' => '10',
                'localityId' => '11',
                'street' => 'Fabrica de Glucoza',
                'streetNo' => '11C',
                'zipCode' => '020331',
                'building' => 'B1',
                'entrance' => 'A',
                'floor' => '2',
                'apartment' => '4',
            ],
        ];
    }

    #[Test]
    public function it_exposes_the_constructor_values(): void
    {
        $branch = new Branch($this->data());

        $this->assertSame('10', $branch->getId());
        $this->assertSame('Bucuresti', $branch->getName());
        $this->assertSame('BCR', $branch->getBank());
        $this->assertSame('RO00BCR0000000000000000', $branch->getBankAccount());
        $this->assertSame('branch@example.com', $branch->getEmail());
        $this->assertSame('0210000000', $branch->getPhone());
        $this->assertSame('0210000001', $branch->getSecondaryPhone());
        $this->assertSame('Ion Popescu', $branch->getContactPerson());
    }

    #[Test]
    public function it_exposes_the_address_values(): void
    {
        $branch = new Branch($this->data());

        $this->assertSame('Bucuresti', $branch->getCounty());
        $this->assertSame('Bucuresti', $branch->getCity());
        $this->assertSame('10', $branch->getCountyId());
        $this->assertSame('11', $branch->getCityId());
        $this->assertSame('Fabrica de Glucoza', $branch->getStreet());
        $this->assertSame('11C', $branch->getStreetNo());
        // UPGRADE_PLAN §7 #17 — postal code is read from the camelCase key.
        $this->assertSame('020331', $branch->getPostalCode());
        $this->assertSame('B1', $branch->getBuilding());
        $this->assertSame('A', $branch->getEntrance());
        $this->assertSame('2', $branch->getFloor());
        $this->assertSame('4', $branch->getApartment());
    }

    #[Test]
    public function it_reads_the_lowercase_zipcode_key(): void
    {
        // UPGRADE_PLAN §7 #17 / Appendix C — /reports/branches returns "zipcode".
        $data = $this->data();
        unset($data['address']['zipCode']);
        $data['address']['zipcode'] = '020331';

        $branch = new Branch($data);

        $this->assertSame('020331', $branch->getPostalCode());
    }

    #[Test]
    public function it_defaults_the_postal_code_when_no_key_is_present(): void
    {
        $data = $this->data();
        unset($data['address']['zipCode']);

        $this->assertSame('', (new Branch($data))->getPostalCode());
    }
}
