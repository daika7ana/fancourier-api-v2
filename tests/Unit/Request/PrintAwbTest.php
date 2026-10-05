<?php

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\PrintAwb;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PrintAwbTest extends TestCase
{
    private function request(): PrintAwb
    {
        return (new PrintAwb())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));
    }

    #[Test]
    public function it_packs_pdf_mode_by_default(): void
    {
        // UPGRADE_PLAN §7 #2/#13 — Phase 3: getSize() returns lang, setHtml(true) ignores its argument.
        $this->assertSame(
            ['clientId' => 12345, 'awbs' => [], 'language' => 'ro', 'pdf' => 1],
            $this->request()->pack()
        );
    }

    #[Test]
    public function it_packs_multiple_awbs_language_and_format(): void
    {
        $packed = $this->request()->addAwb('A1')->addAwb('A2')->setLang('en')->setSize('A4')->pack();

        $this->assertSame(
            ['clientId' => 12345, 'awbs' => ['A1', 'A2'], 'language' => 'en', 'pdf' => 1, 'format' => 'A4'],
            $packed
        );
    }

    #[Test]
    public function it_packs_zpl_mode_with_dpi_instead_of_pdf(): void
    {
        $packed = $this->request()->setZpl(true)->setDpi(203)->pack();

        $this->assertSame(
            ['clientId' => 12345, 'awbs' => [], 'language' => 'ro', 'zpl' => 1, 'dpi' => 203],
            $packed
        );
    }
}
