<?php

namespace OliviaRouter;

class ControllerFactory
{
    public static function createController(string $controllerName)
    {
        $callables = explode('#', $controllerName);
        $controllerParts = array_map('ucfirst', explode('/', $callables[0]));
        $controllerClass = implode('\\', $controllerParts);
        $controllerInstance = new $controllerClass();
        return $controllerInstance;
    }
}
