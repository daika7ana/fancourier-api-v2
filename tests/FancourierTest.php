<?php

declare(strict_types=1);

namespace Fancourier\Tests;

use Fancourier\Fancourier;
use Fancourier\Request\CreateAwb;
use Fancourier\Request\DeleteAwb;
use Fancourier\Request\GetCosts;
use Fancourier\Request\PrintAwb;
use Fancourier\Request\TrackAwb;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Live integration tests. These hit https://api.fancourier.ro/, create and
 * delete real AWBs on a shared test account, and require network access.
 * They are excluded by default (see phpunit.xml.dist) and only run when
 * FANCOURIER_LIVE_TOKEN is set, e.g.:
 *
 *   FANCOURIER_TEST_CLIENT_ID=... FANCOURIER_TEST_USERNAME=... \
 *   FANCOURIER_TEST_PASSWORD=... FANCOURIER_LIVE_TOKEN=... \
 *   vendor/bin/phpunit --group integration
 */
#[Group('integration')]
class FancourierTest extends TestCase
{
    private Fancourier $fan;

    protected function setUp(): void
    {
        if ((string) getenv('FANCOURIER_LIVE_TOKEN') === '') {
            $this->markTestSkipped('Set FANCOURIER_LIVE_TOKEN to run live integration tests.');
        }

        $this->fan = new Fancourier(
            (string) getenv('FANCOURIER_TEST_CLIENT_ID'),
            (string) getenv('FANCOURIER_TEST_USERNAME'),
            (string) getenv('FANCOURIER_TEST_PASSWORD'),
        );
    }

    #[Test]
    public function it_can_get_costs(): void
    {
        $request = new GetCosts();
        $request
            ->setParcels(1)
            ->setWeight(2)
            ->setCounty('Arad')
            ->setCity('Aciuta')
            ->setDeclaredValue(125);

        $response = $this->fan->getCosts($request);

        $this->assertTrue($response->isOk());
        $this->assertIsArray($response->getData());
    }

    #[Test]
    public function it_can_create_an_awb(): void
    {
        $awb = new \Fancourier\Objects\AwbIntern();
        $awb
            ->setParcels(1)
            ->setWeight(2)
            ->setReimbursement(125)
            ->setDeclaredValue(125)
            ->setSizes(10, 5, 1) // in cm // or use setLength(), setHeight(), setWidth()
            ->setNotes('testing notes')
            ->setContents('SKU-1, SKU-2')
            ->setRecipientName('John Ivy')
            ->setPhone('0723000000')
            ->setCounty('Arad')
            ->setCity('Aciuta')
            ->setStreet('Str Lunga')
            ->setNumber('1');

        $request = new CreateAwb();
        $request->addAwb($awb);

        $response = $this->fan->createAwb($request);

        $this->assertTrue($response->isOk());
        $this->assertIsArray($response->getData());
        $this->assertIsInt($awb->getAwb());
    }

    #[Test]
    public function it_can_track_an_existing_awb(): void
    {
        $request = new TrackAwb();
        $request->setAwb('2347300120337');

        $response = $this->fan->trackAwb($request);

        $this->assertTrue($response->isOk());
        $this->assertIsArray($response->getData());
    }

    #[Test]
    public function it_can_get_a_printable_pdf_awb(): void
    {
        $request = new PrintAwb();
        $request->setPdf(true)->setAwb('2347300120337');

        $response = $this->fan->printAwb($request);

        $this->assertTrue($response->isOk());
        $this->assertIsString($response->getData());
    }

    #[Test]
    public function it_can_get_a_html_version_for_an_awb(): void
    {
        $request = new PrintAwb();
        $request->setPdf(false)->setAwb('2347300120337');

        $response = $this->fan->printAwb($request);

        $this->assertTrue($response->isOk());
        $this->assertIsString($response->getData());
    }

    #[Test]
    public function it_can_delete_an_existing_awb(): void
    {
        $request = new DeleteAwb();
        $request->setAwb('2347300120340');

        $response = $this->fan->deleteAwb($request);

        $this->assertIsBool($response->getData());
    }

    // Bulk tracking (trackAwbBulk / TrackAwbBulk) is not implemented in the
    // current API surface; the previously commented-out test referenced a
    // nonexistent class.
}
