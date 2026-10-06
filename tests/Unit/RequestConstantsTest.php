<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit;

use Fancourier\Request\AbstractRequest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Asserts the additive code-list constants (#24) carry the documented values.
 */
final class RequestConstantsTest extends TestCase
{
    #[Test]
    public function payment_type_constants_match_the_spec(): void
    {
        $this->assertConstants([
            'TYPE_SENDER' => 'expeditor',
            'TYPE_RECIPIENT' => 'destinatar',
            'TYPE_OTHER' => 'Altul',
        ]);
    }

    #[Test]
    public function service_option_constants_match_the_spec(): void
    {
        $this->assertConstants([
            'OPTION_OPEN_ON_DELIVERY' => 'A',
            'OPTION_OPOD' => 'B',
            'OPTION_DROP_OFF_OFFICE' => 'C',
            'OPTION_PICKUP_OFFICE' => 'D',
            'OPTION_DROP_OFF_PAYPOINT' => 'E',
            'OPTION_DELIVERY_PAYPOINT' => 'F',
            'OPTION_SMS_BUSINESS' => 'M',
            'OPTION_PICKUP_PREALERT' => 'O',
            'OPTION_PREALERT' => 'P',
            'OPTION_SATURDAY_DELIVERY' => 'S',
            'OPTION_PICKUP_LOCKER' => 'V',
            'OPTION_DROP_OFF_LOCKER' => 'W',
            'OPTION_EPOD' => 'X',
            'OPTION_MPOS' => 'Y',
        ]);
    }

    #[Test]
    public function order_type_constants_match_the_spec(): void
    {
        $this->assertConstants([
            'ORDER_TYPE_STANDARD' => 'Standard',
            'ORDER_TYPE_EXPRESS_LOCO_1H' => 'Express Loco 1h',
            'ORDER_TYPE_EXPRESS_LOCO_2H' => 'Express Loco 2h',
            'ORDER_TYPE_EXPRESS_LOCO_4H' => 'Express Loco 4h',
            'ORDER_TYPE_EXPRESS_LOCO_6H' => 'Express Loco 6h',
        ]);
    }

    #[Test]
    public function service_type_constants_match_the_spec(): void
    {
        $this->assertConstants([
            'SERVICE_STANDARD' => 'Standard',
            'SERVICE_REDCODE' => 'RedCode',
            'SERVICE_CASH_ON_DELIVERY' => 'Cont Colector',
            'SERVICE_EXPRESS_LOCO_2H' => 'Express Loco 2H',
            'SERVICE_EXPRESS_LOCO_4H' => 'Express Loco 4H',
            'SERVICE_EXPRESS_LOCO_6H' => 'Express Loco 6H',
            'SERVICE_EXPORT' => 'Export',
            'SERVICE_REDCODE_CASH_ON_DELIVERY' => 'Red code-Cont Colector',
            'SERVICE_EXPRESS_LOCO_2H_CASH_ON_DELIVERY' => 'Express Loco 2H-Cont Colector',
            'SERVICE_EXPRESS_LOCO_4H_CASH_ON_DELIVERY' => 'Express Loco 4H-Cont Colector',
            'SERVICE_EXPRESS_LOCO_6H_CASH_ON_DELIVERY' => 'Express Loco 6H-Cont Colector',
            'SERVICE_EXPRESS_LOCO_1H' => 'Express Loco 1H',
            'SERVICE_EXPRESS_LOCO_1H_CASH_ON_DELIVERY' => 'Express Loco 1H-Cont Colector',
            'SERVICE_EXPORT_CASH_ON_DELIVERY' => 'Export-Cont Colector',
            'SERVICE_COLLECT_POINT' => 'CollectPoint',
            'SERVICE_COLLECT_POINT_CASH_ON_DELIVERY' => 'CollectPoint Cont Colector',
            'SERVICE_WHITE_GOODS' => 'Produse Albe',
            'SERVICE_WHITE_GOODS_CASH_ON_DELIVERY' => 'Produse Albe-Cont Colector',
            'SERVICE_FREIGHT' => 'Transport Marfa',
            'SERVICE_FREIGHT_CASH_ON_DELIVERY' => 'Transport Marfa-Cont Colector',
            'SERVICE_FREIGHT_WHITE_GOODS' => 'Transport Marfa Produse Albe',
            'SERVICE_FREIGHT_WHITE_GOODS_CASH_ON_DELIVERY' => 'Transport Marfa Produse Albe-Cont Colector',
            'SERVICE_FANBOX' => 'FANbox',
            'SERVICE_FANBOX_CASH_ON_DELIVERY' => 'FANbox Cont Colector',
        ]);
    }

    #[Test]
    public function awb_event_constants_match_the_spec(): void
    {
        $this->assertConstants([
            'AWB_EVENT_C0' => 'C0',
            'AWB_EVENT_C1' => 'C1',
            'AWB_EVENT_H0' => 'H0',
            'AWB_EVENT_H1' => 'H1',
            'AWB_EVENT_H2' => 'H2',
            'AWB_EVENT_H3' => 'H3',
            'AWB_EVENT_H4' => 'H4',
            'AWB_EVENT_H10' => 'H10',
            'AWB_EVENT_H11' => 'H11',
            'AWB_EVENT_H12' => 'H12',
            'AWB_EVENT_H13' => 'H13',
            'AWB_EVENT_H15' => 'H15',
            'AWB_EVENT_H17' => 'H17',
            'AWB_EVENT_S1' => 'S1',
            'AWB_EVENT_S2' => 'S2',
            'AWB_EVENT_S3' => 'S3',
            'AWB_EVENT_S4' => 'S4',
            'AWB_EVENT_S5' => 'S5',
            'AWB_EVENT_S6' => 'S6',
            'AWB_EVENT_S7' => 'S7',
            'AWB_EVENT_S8' => 'S8',
            'AWB_EVENT_S9' => 'S9',
            'AWB_EVENT_S10' => 'S10',
            'AWB_EVENT_S11' => 'S11',
            'AWB_EVENT_S12' => 'S12',
            'AWB_EVENT_S14' => 'S14',
            'AWB_EVENT_S15' => 'S15',
            'AWB_EVENT_S16' => 'S16',
            'AWB_EVENT_S19' => 'S19',
            'AWB_EVENT_S20' => 'S20',
            'AWB_EVENT_S21' => 'S21',
            'AWB_EVENT_S22' => 'S22',
            'AWB_EVENT_S24' => 'S24',
            'AWB_EVENT_S25' => 'S25',
            'AWB_EVENT_S27' => 'S27',
            'AWB_EVENT_S28' => 'S28',
            'AWB_EVENT_S30' => 'S30',
            'AWB_EVENT_S33' => 'S33',
            'AWB_EVENT_S35' => 'S35',
            'AWB_EVENT_S37' => 'S37',
            'AWB_EVENT_S38' => 'S38',
            'AWB_EVENT_S42' => 'S42',
            'AWB_EVENT_S43' => 'S43',
            'AWB_EVENT_S46' => 'S46',
            'AWB_EVENT_S47' => 'S47',
            'AWB_EVENT_S49' => 'S49',
            'AWB_EVENT_S50' => 'S50',
        ]);
    }

    #[Test]
    public function order_event_constants_match_the_spec(): void
    {
        $this->assertConstants([
            'ORDER_EVENT_PENDING' => 0,
            'ORDER_EVENT_PLACED' => 1,
            'ORDER_EVENT_PICKED_UP' => 2,
            'ORDER_EVENT_NOT_PICKED_UP' => 3,
            'ORDER_EVENT_CANCELLED' => 4,
            'ORDER_EVENT_POSTPONED' => 5,
            'ORDER_EVENT_SENDER_NOT_FOUND' => 8,
            'ORDER_EVENT_PICKED_UP_BORDEROU' => 12,
            'ORDER_EVENT_CANCELLATION_IN_PROGRESS' => 99,
        ]);
    }
    /**
     * @param array<string, string|int> $expected
     */
    private function assertConstants(array $expected): void
    {
        foreach ($expected as $name => $value) {
            $this->assertTrue(
                defined(AbstractRequest::class . '::' . $name),
                'Missing constant AbstractRequest::' . $name,
            );
            $this->assertSame($value, constant(AbstractRequest::class . '::' . $name), $name);
        }
    }
}
