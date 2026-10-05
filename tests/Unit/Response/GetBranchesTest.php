<?php

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Objects\Branch;
use Fancourier\Response\GetBranches;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GetBranchesTest extends TestCase
{
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }

    #[Test]
    public function it_parses_a_success_body(): void
    {
        // UPGRADE_PLAN §7 #8 — get($id): array returns Branch (or false on miss); Phase 3.
        // Read via getAll() until the return type is fixed.
        $response = (new GetBranches())->setData($this->fixture('getBranches.success'));

        $this->assertTrue($response->isOk());
        $this->assertInstanceOf(Branch::class, $response->getAll()['1']);
        $this->assertSame('Bucuresti', $response->getAll()['1']->getName());
        $this->assertSame('Bucuresti', $response->getAll()['1']->getCity());
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
        // UPGRADE_PLAN §7 #8 — get($id) returns false on miss (TypeError); Phase 3. Do not exercise.
        $response = (new GetBranches())->setData($this->fixture('getBranches.missing-keys'));

        $this->assertTrue($response->isOk());
        $this->assertSame([], $response->getAll());
    }
}
