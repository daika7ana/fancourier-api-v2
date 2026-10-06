<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit;

use Fancourier\Fancourier;
use Fancourier\Objects\AwbTracker;
use Fancourier\Request\TrackAwb as TrackAwbRequest;
use Fancourier\Response\TrackAwb as TrackAwbResponse;
use Fancourier\Tests\Support\FakeClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The facade's trackAwb() must expose the typed TrackAwb response.
 */
class FancourierTrackAwbTest extends TestCase
{
    #[Test]
    public function track_awb_returns_the_typed_response(): void
    {
        $client = (new FakeClient())->setResponse(
            (string) file_get_contents(__DIR__ . '/../fixtures/trackAwb.success.json'),
        );

        $fancourier = (new Fancourier('1', 'u', 'p', 'test-token'))->setClient($client);

        $response = $fancourier->trackAwb(new TrackAwbRequest());

        $this->assertInstanceOf(TrackAwbResponse::class, $response);
        $this->assertTrue($response->isOk());
        $this->assertInstanceOf(AwbTracker::class, $response->getAwb(2347300120337));
    }
}
