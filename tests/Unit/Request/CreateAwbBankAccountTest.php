<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Request\CreateAwbBankAccount;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CreateAwbBankAccountTest extends TestCase
{
    #[Test]
    public function it_packs_an_empty_bare_list_by_default(): void
    {
        $this->assertSame([], (new CreateAwbBankAccount())->pack());
    }

    #[Test]
    public function it_packs_the_records_as_a_bare_list(): void
    {
        $request = (new CreateAwbBankAccount())
            ->addRecord('AWB-1', 'RO49AAAA1B31007593840000')
            ->addRecord('AWB-2', 'RO49AAAA1B31007593840001');

        $this->assertSame(
            [
                ['awb' => 'AWB-1', 'iban' => 'RO49AAAA1B31007593840000'],
                ['awb' => 'AWB-2', 'iban' => 'RO49AAAA1B31007593840001'],
            ],
            $request->pack(),
        );
        $this->assertSame($request->pack(), $request->getRecords());
    }
}
