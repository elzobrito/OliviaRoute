<?php

namespace OliviaRouter;

class FallbackContextStore implements ContextStoreInterface
{
    /**
     * @var ContextStoreInterface[]
     */
    private array $stores;

    public function __construct(ContextStoreInterface ...$stores)
    {
        $this->stores = $stores;
    }

    public function get(string $key, $default = null)
    {
        foreach ($this->stores as $store) {
            if ($store->has($key)) {
                return $store->get($key, $default);
            }
        }

        return $default;
    }

    public function has(string $key): bool
    {
        foreach ($this->stores as $store) {
            if ($store->has($key)) {
                return true;
            }
        }

        return false;
    }
}
