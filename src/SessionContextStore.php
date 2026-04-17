<?php

namespace OliviaRouter;

class SessionContextStore extends ArrayContextStore
{
    public function __construct(?array $session = null)
    {
        parent::__construct($session ?? (isset($_SESSION) && is_array($_SESSION) ? $_SESSION : []));
    }
}
