<?php

namespace Tests;

abstract class TestCase
{
    public function setUp(): void
    {
    }

    public function tearDown(): void
    {
    }

    protected function assertTrue(bool $condition, string $message = 'Expected true'): void
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    protected function assertFalse(bool $condition, string $message = 'Expected false'): void
    {
        if ($condition) {
            throw new \RuntimeException($message);
        }
    }

    protected function assertSame($expected, $actual, string $message = ''): void
    {
        if ($expected !== $actual) {
            $suffix = $message !== '' ? $message . ' ' : '';
            throw new \RuntimeException(
                $suffix . 'Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true)
            );
        }
    }

    protected function assertArrayHasKey(string $key, array $array, string $message = ''): void
    {
        if (!array_key_exists($key, $array)) {
            throw new \RuntimeException($message !== '' ? $message : "Missing array key {$key}");
        }
    }

    protected function expectException(string $className, callable $callback, ?string $messageContains = null): void
    {
        try {
            $callback();
        } catch (\Throwable $throwable) {
            if (!$throwable instanceof $className) {
                throw new \RuntimeException(
                    'Expected exception ' . $className . ', got ' . get_class($throwable) . ': ' . $throwable->getMessage()
                );
            }

            if ($messageContains !== null && strpos($throwable->getMessage(), $messageContains) === false) {
                throw new \RuntimeException(
                    'Expected exception message containing ' . $messageContains . ', got ' . $throwable->getMessage()
                );
            }

            return;
        }

        throw new \RuntimeException('Expected exception ' . $className . ' was not thrown.');
    }
}
