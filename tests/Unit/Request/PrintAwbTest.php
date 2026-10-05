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
        $this->assertSame(
            ['clientId' => 12345, 'awbs' => [], 'language' => 'ro', 'pdf' => 1],
            $this->request()->pack()
        );
    }

    #[Test]
    public function it_returns_the_configured_size(): void
    {
        $this->assertSame('A5', $this->request()->setSize('A5')->getSize());
    }

    #[Test]
    public function it_honours_the_html_activation_argument(): void
    {
        $request = $this->request();

        $request->setHtml(true);
        $this->assertTrue($request->getHtml());
        $this->assertFalse($request->getPdf());
        $this->assertFalse($request->getZpl());
        $this->assertArrayNotHasKey('pdf', $request->pack());
        $this->assertArrayNotHasKey('zpl', $request->pack());

        $request->setHtml(false);
        $this->assertFalse($request->getHtml());
        $this->assertTrue($request->getPdf());
        $this->assertSame(1, $request->pack()['pdf']);
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
