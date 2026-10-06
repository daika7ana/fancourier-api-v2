<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit;

use Fancourier\Auth;
use Fancourier\Tests\Support\ArrayLogger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Token-lifecycle logging never carries credentials.
 */
final class AuthLoggingTest extends TestCase
{
    #[Test]
    public function a_failed_retrieval_logs_a_warning_without_secrets(): void
    {
        $logger = new ArrayLogger();
        $auth = new class ('1', 'user', 'hunter2-password', '') extends Auth {
            protected function retrieve_token(): bool
            {
                throw new \Exception('login rejected');
            }
        };
        $auth->setLogger($logger);

        $this->assertFalse($auth->getToken());

        $this->assertCount(1, $logger->records);
        $this->assertSame('warning', $logger->records[0]['level']);
        $this->assertSame('login rejected', $logger->records[0]['context']['error']);

        $encoded = (string) json_encode($logger->records);
        $this->assertStringNotContainsString('hunter2-password', $encoded);
        $this->assertStringNotContainsString('user', $encoded);
    }

    #[Test]
    public function a_successful_retrieval_logs_debug_with_expiry(): void
    {
        $logger = new ArrayLogger();
        $auth = new class ('1', 'user', 'hunter2-password', '') extends Auth {
            protected function retrieve_token(): bool
            {
                (new \ReflectionProperty(Auth::class, 'btoken'))->setValue($this, 'secret-token');
                (new \ReflectionProperty(Auth::class, 'btoken_expires_at'))
                    ->setValue($this, '2030-01-01 00:00:00');

                return true;
            }
        };
        $auth->setLogger($logger);

        $this->assertSame('secret-token', $auth->getToken());

        $this->assertCount(1, $logger->records);
        $this->assertSame('debug', $logger->records[0]['level']);
        $this->assertSame('2030-01-01 00:00:00', $logger->records[0]['context']['expires_at']);

        $encoded = (string) json_encode($logger->records);
        $this->assertStringNotContainsString('secret-token', $encoded);
        $this->assertStringNotContainsString('hunter2-password', $encoded);
    }
}
