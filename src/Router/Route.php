<?php

namespace OliviaRouter\Router;

use OliviaRouter\Request\Request;
use OliviaRouter\Trie\Trie;

class Route
{
    private $pattern;
    private $controllerMethod;
    private $params;

    public function __construct($httpMethod, $pattern, $controllerMethod)
    {
        $this->httpMethod = $httpMethod;
        $this->pattern = $pattern;
        $this->controllerMethod = $controllerMethod;
    }

    public function matches(Request $request, Trie $trie)
    {
        $matches = $trie->search($this->pattern, $request->getUri());
    
        if ($matches) {
            $this->params = array_values($matches);
            array_shift($this->params); // Remove the full match
            return true;
        }
    
        return false;
    }

    public function getParams()
    {
        return $this->params ?? [];
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function getControllerMethod()
    {
        return $this->controllerMethod;
    }
}
