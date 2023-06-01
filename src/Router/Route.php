<?php

namespace OliviaRouter\Router;

use OliviaRouter\Request\Request;

class Route
{
    private $httpMethod;
    private $pattern;
    private $controllerMethod;
    private $params;

    public function __construct($httpMethod, $pattern, $controllerMethod)
    {
        $this->httpMethod = $httpMethod;
        $this->pattern = $pattern;
        $this->controllerMethod = $controllerMethod;
    }

    public function matches(Request $request)
    {
        return $request->getMethod() === $this->httpMethod &&
            preg_match($this->pattern, $request->getUri(), $this->params) === 1;
    }

    public function getParams()
    {
        $params = array_values($this->params);
        array_shift($params); // Remove the full match
        return $params;
    }

    public function getControllerMethod()
    {
        return $this->controllerMethod;
    }
}