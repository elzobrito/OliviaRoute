<?php

namespace OliviaRouter\Request;

class Request
{
    private $method;
    private $uri;

    public function __construct($method, $uri)
    {
        $this->method = $method;
        $this->uri = $uri;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getUri()
    {
        return $this->uri;
    }
}