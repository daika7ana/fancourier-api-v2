<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Objects\Branch;
use Fancourier\Response\GetBranches;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GetBranchesTest extends TestCase
{
    #[Test]
    public function it_parses_a_success_body(): void
    {
        $response = (new GetBranches())->setData($this->fixture('getBranches.success'));

        $this->assertTrue($response->isOk());
        $this->assertInstanceOf(Branch::class, $response->get('1'));
        $this->assertSame('Bucuresti', $response->get('1')->getName());
        $this->assertSame('Bucuresti', $response->get('1')->getCity());
    }

    /**
     * Defect #8 (UPGRADE_PLAN §7): get($id) declared array but returned false on
     * a miss; it now returns ?Branch (null on a miss).
     */
    #[Test]
    public function it_returns_null_for_an_unknown_branch(): void
    {
        $response = (new GetBranches())->setData($this->fixture('getBranches.success'));

        $this->assertNull($response->get('does-not-exist'));
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new GetBranches())->setData($this->fixture('getBranches.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('No branches', $response->getErrorMessage());
    }

    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        $response = (new GetBranches())->setData($this->fixture('getBranches.missing-keys'));

        $this->assertTrue($response->isOk());
        $this->assertSame([], $response->getAll());
        $this->assertNull($response->get('1'));
    }
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }
}
