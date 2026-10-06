<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Defect #22 (UPGRADE_PLAN §7): every response parser must set the error state
 * for a non-success body, even when the body omits the error fields, and must
 * never report isOk() === true on such a body.
 *
 * PrintAwb is excluded from the malformed-body case: a non-JSON body is a valid
 * binary (PDF) success for that endpoint.
 */
class ErrorStateMatrixTest extends TestCase
{
    /**
     * @return array<string, array{0: class-string<Response\Generic>}>
     */
    public static function responseClasses(): array
    {
        $shortNames = [
            'CreateAwb', 'CreateAwbExternal', 'CreateCourierOrder',
            'DeleteAwb', 'DeleteCourierOrder', 'GetAwbConfirmations',
            'GetAwbEvents', 'GetBankTransfers', 'GetBranches', 'GetCities',
            'GetCitiesExternal', 'GetCosts', 'GetCostsExternal', 'GetCounties',
            'GetCountiesExternal', 'GetCountries', 'GetCourierOrderEvents',
            'GetCourierOrders', 'GetPudo', 'GetServiceOptions', 'GetServices',
            'GetShippingSlip', 'GetStreets', 'PrintAwb', 'TrackAwb',
            'TrackCourierOrder',
        ];

        $classes = [];
        foreach ($shortNames as $shortName) {
            $classes[$shortName] = ['Fancourier\\Response\\' . $shortName];
        }

        return $classes;
    }

    /**
     * @return array<string, array{0: class-string<Response\Generic>}>
     */
    public static function nonBinaryResponseClasses(): array
    {
        return array_diff_key(self::responseClasses(), ['PrintAwb' => true]);
    }

    #[Test]
    #[DataProvider('responseClasses')]
    public function it_reports_failure_when_status_is_fail_without_error_fields(string $class): void
    {
        $response = (new $class())->setData($this->fixture('generic.status-fail'));

        $this->assertFalse($response->isOk());
        $this->assertNotEmpty($response->getErrorCode());
        $this->assertNotSame('', $response->getErrorMessage());
    }

    #[Test]
    #[DataProvider('responseClasses')]
    public function it_reports_failure_when_the_status_is_missing(string $class): void
    {
        $response = (new $class())->setData($this->fixture('generic.empty'));

        $this->assertFalse($response->isOk());
        $this->assertNotEmpty($response->getErrorCode());
        $this->assertNotSame('', $response->getErrorMessage());
    }

    #[Test]
    #[DataProvider('nonBinaryResponseClasses')]
    public function it_reports_failure_on_a_malformed_body(string $class): void
    {
        $response = (new $class())->setData('not-json');

        $this->assertFalse($response->isOk());
        $this->assertNotEmpty($response->getErrorCode());
        $this->assertSame('not-json', $response->getErrorMessage());
    }

    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }
}
