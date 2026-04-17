<?php

namespace OliviaRouter;

class CookieContextStore extends ArrayContextStore
{
    public function __construct(?array $cookies = null)
    {
        parent::__construct($cookies ?? $_COOKIE ?? []);
    }
}
