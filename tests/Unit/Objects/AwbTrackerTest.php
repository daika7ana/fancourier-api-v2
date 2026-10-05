<?php

namespace Fancourier\Tests\Unit\Objects;

use Fancourier\Objects\AwbTracker;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AwbTrackerTest extends TestCase
{
    #[Test]
    public function it_exposes_tracking_details_and_events(): void
    {
        $tracker = new AwbTracker([
            'awbNumber' => '2000000000082',
            'content' => 'Order #135',
            'date' => '2023-11-28 00:00:00',
            'paymentDate' => '2023-12-01 00:00:00',
            'returnAwbNumber' => '2000000000099',
            'redirectionAwbNumber' => '2000000000098',
            'reimbursementAwbNumber' => '2000000000097',
            'oPODAwbNumber' => '2000000000096',
            'confirmation' => ['name' => 'Ion Popescu', 'date' => '2023-11-29'],
            'OTD' => '24H',
            'events' => [
                ['id' => 'C0', 'name' => 'Expeditie ridicata', 'location' => 'Bucuresti', 'date' => '2023-11-28 18:58:00'],
                ['id' => 'S1', 'name' => 'Expeditie in livrare', 'location' => 'Targu Frumos', 'date' => '2023-11-29 09:17:00'],
            ],
        ]);

        $this->assertSame('2000000000082', $tracker->getAwbNumber());
        $this->assertSame('Order #135', $tracker->getContent());
        $this->assertSame('2000000000099', $tracker->getReturnAwbNumber());
        $this->assertSame('2000000000098', $tracker->getRedirectionAwbNumber());
        $this->assertSame('2000000000097', $tracker->getReimbursementAwbNumber());
        $this->assertSame('2000000000096', $tracker->getOPODAwbNumber());
        // UPGRADE_PLAN §7 #21 — paymentDate is parsed and now exposed.
        $this->assertSame('2023-12-01 00:00:00', $tracker->getPaymentDate());
        $this->assertSame('24H', $tracker->getOTD());
        $this->assertTrue($tracker->hasConfirmation());
        $this->assertSame(['name' => 'Ion Popescu', 'date' => '2023-11-29'], $tracker->getConfirmation());
        $this->assertCount(2, $tracker->getEvents());
        $this->assertSame(['id' => 'S1', 'name' => 'Expeditie in livrare', 'location' => 'Targu Frumos', 'date' => '2023-11-29 09:17:00'], $tracker->getStatus());
    }

    #[Test]
    public function it_reports_a_message_when_there_are_no_events(): void
    {
        $tracker = new AwbTracker([
            'awbNumber' => '2000000000000',
            'message' => 'The AWB has been registerd by sender',
        ]);

        $this->assertSame('The AWB has been registerd by sender', $tracker->getMessage());
        $this->assertSame('', $tracker->getReturnAwbNumber());
        $this->assertFalse($tracker->hasConfirmation());
        $this->assertSame([], $tracker->getEvents());

        $status = $tracker->getStatus();
        $this->assertNull($status['id']);
        $this->assertSame('The AWB has been registerd by sender', $status['name']);
        $this->assertSame('', $status['location']);
    }

    #[Test]
    public function it_defaults_optional_fields_when_data_is_minimal(): void
    {
        $tracker = new AwbTracker(['awbNumber' => '1']);

        $this->assertSame('1', $tracker->getAwbNumber());
        $this->assertSame('', $tracker->getContent());
        $this->assertSame([], $tracker->getConfirmation());
        $this->assertSame('', $tracker->getOTD());
        // UPGRADE_PLAN §7 #21 — getter defaults to '' when absent.
        $this->assertSame('', $tracker->getPaymentDate());
    }
}
