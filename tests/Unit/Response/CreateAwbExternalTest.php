<?php

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Objects\AwbExtern;
use Fancourier\Response\CreateAwbExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CreateAwbExternalTest extends TestCase
{
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }

    #[Test]
    public function it_parses_a_success_body(): void
    {
        $response = new CreateAwbExternal();
        $response->setAwbList([new AwbExtern()]);
        $response->setData($this->fixture('createAwbExternal.success'));

        $this->assertTrue($response->isOk());
        $this->assertCount(1, $response->getAll());
        $this->assertInstanceOf(AwbExtern::class, $response->getAll()[0]);
        $this->assertSame(2347300120340, $response->getAll()[0]->getAwb());
        $this->assertFalse($response->getAll()[0]->hasErrors());
    }

    /**
     * Defect #6 (UPGRADE_PLAN §7, uninventoried): the JSON error branch used to
     * read $response_json['message'] unconditionally on an empty body, emitting
     * an undefined-key warning. It now routes through Generic::setErrorFromBody().
     */
    #[Test]
    public function it_reports_an_error_on_an_empty_json_body(): void
    {
        $response = (new CreateAwbExternal())->setData($this->fixture('createAwbExternal.empty'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('Unknown error', $response->getErrorMessage());
    }

    /**
     * A malformed (non-JSON) body exercises the outer error branch; it must not
     * emit an undefined-key warning either.
     */
    #[Test]
    public function it_reports_an_error_on_a_malformed_body(): void
    {
        $response = (new CreateAwbExternal())->setData('not-json');

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('not-json', $response->getErrorMessage());
    }

    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        $response = new CreateAwbExternal();
        $response->setAwbList([new AwbExtern()]);
        $response->setData($this->fixture('createAwbExternal.missing-keys'));

        $this->assertTrue($response->isOk());
        $this->assertCount(1, $response->getAll());
        $this->assertSame(2347300120340, $response->getAll()[0]->getAwb());
        $this->assertFalse($response->getAll()[0]->hasErrors());
    }
}
