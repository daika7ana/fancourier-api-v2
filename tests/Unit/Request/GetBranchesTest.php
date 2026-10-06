<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\GetBranches;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GetBranchesTest extends TestCase
{
    private function request(): GetBranches
    {
        return (new GetBranches())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));
    }

    #[Test]
    public function it_packs_an_empty_query_by_default(): void
    {
        $this->assertSame([], $this->request()->pack());
    }

    #[Test]
    public function it_packs_county_and_locality_when_set(): void
    {
        $packed = $this->request()->setCounty('Cluj')->setCity('Cluj-Napoca')->pack();

        $this->assertSame(['county' => 'Cluj', 'locality' => 'Cluj-Napoca'], $packed);
    }
}
