<?php

require __DIR__ . '/bootstrap.php';

$suite = new \Tests\RouterTest();
$reflection = new ReflectionClass($suite);
$methods = array_filter(
    $reflection->getMethods(ReflectionMethod::IS_PUBLIC),
    function (ReflectionMethod $method): bool {
        return strpos($method->getName(), 'test_') === 0;
    }
);

$failures = [];

foreach ($methods as $method) {
    try {
        $suite->setUp();
        $suite->{$method->getName()}();
        $suite->tearDown();
        echo '[PASS] ' . $method->getName() . PHP_EOL;
    } catch (Throwable $throwable) {
        $failures[] = [
            'test' => $method->getName(),
            'message' => $throwable->getMessage(),
        ];
        echo '[FAIL] ' . $method->getName() . ': ' . $throwable->getMessage() . PHP_EOL;
    }
}

if ($failures !== []) {
    echo PHP_EOL . count($failures) . ' test(s) failed.' . PHP_EOL;
    exit(1);
}

echo PHP_EOL . count($methods) . ' test(s) passed.' . PHP_EOL;
