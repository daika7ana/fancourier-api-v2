<?php

declare(strict_types=1);

namespace Fancourier\Tests\Canary;

use Fancourier\Auth;
use Fancourier\Client;
use Fancourier\Fancourier;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Phase 3b W6: pins the 2.0 removal contract. These symbols were public in the
 * v1 surface and are purposefully gone; a regression that re-adds one (or the
 * consumer codemod accidentally renaming an old call) must fail here.
 */
final class RemovalContractTest extends TestCase
{
    #[Test]
    public function removed_client_methods_are_gone(): void
    {
        $this->assertFalse(method_exists(Client::class, 'get_error_no'));
        $this->assertFalse(method_exists(Client::class, 'headers_reset'));
    }

    #[Test]
    public function removed_auth_methods_are_gone(): void
    {
        $this->assertFalse(method_exists(Auth::class, 'getClientUsername'));
        $this->assertFalse(method_exists(Auth::class, 'getClientPassword'));
    }

    #[Test]
    public function removed_facade_test_instance_is_gone(): void
    {
        $this->assertFalse(method_exists(Fancourier::class, 'testInstance'));
    }

    #[Test]
    public function removed_test_credential_constants_are_undefined(): void
    {
        $this->assertFalse(defined(Fancourier::class.'::TEST_CLIENT_ID'));
        $this->assertFalse(defined(Fancourier::class.'::TEST_USERNAME'));
        $this->assertFalse(defined(Fancourier::class.'::TEST_PASSWORD'));
    }

    #[Test]
    public function renamed_client_methods_exist(): void
    {
        $renamed = [
            'setVerify',
            'setTimeout',
            'setPutRequest',
            'setDeleteRequest',
            'postJson',
            'postMultiArray',
            'addHeader',
            'deleteHeader',
            'getError',
        ];

        foreach ($renamed as $method) {
            $this->assertTrue(method_exists(Client::class, $method), $method.' should exist');
        }
    }
}
