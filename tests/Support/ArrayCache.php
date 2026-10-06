<?php

declare(strict_types=1);

namespace Fancourier\Tests\Support;

use Psr\SimpleCache\CacheInterface;

/**
 * Minimal in-memory PSR-16 cache for hermetic tests.
 *
 * TTL is accepted but ignored; tests that need expiry set explicit timestamps.
 * Not final so tests can subclass it to simulate a throwing cache.
 */
class ArrayCache implements CacheInterface
{
    /** @var array<string, mixed> */
    private array $values = [];

    public function get(string $key, mixed $default = null): mixed
    {
        $this->assertValidKey($key);

        return array_key_exists($key, $this->values) ? $this->values[$key] : $default;
    }

    public function set(string $key, mixed $value, int|\DateInterval|null $ttl = null): bool
    {
        $this->assertValidKey($key);
        $this->values[$key] = $value;

        return true;
    }

    public function delete(string $key): bool
    {
        $this->assertValidKey($key);
        unset($this->values[$key]);

        return true;
    }

    public function clear(): bool
    {
        $this->values = [];

        return true;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[(string) $key] = $this->get((string) $key, $default);
        }

        return $result;
    }

    public function setMultiple(iterable $values, int|\DateInterval|null $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set((string) $key, $value, $ttl);
        }

        return true;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete((string) $key);
        }

        return true;
    }

    public function has(string $key): bool
    {
        $this->assertValidKey($key);

        return array_key_exists($key, $this->values);
    }

    private function assertValidKey(string $key): void
    {
        if ($key === '' || preg_match('/[{}()\/\\\\@:]/', $key) === 1) {
            throw new \InvalidArgumentException("Invalid cache key: {$key}");
        }
    }
}
