<?php

namespace OliviaRouter;

interface ContextStoreInterface
{
    public function get(string $key, $default = null);

    public function has(string $key): bool;
}
