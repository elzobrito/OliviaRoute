<?php

namespace OliviaRouter;

class ControllerFactory
{
    public static function createController(string $controllerName): object
    {
        $controllerClass = self::normalizeClassName($controllerName);

        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller {$controllerClass} não encontrado.");
        }

        return new $controllerClass();
    }

    private static function normalizeClassName(string $className): string
    {
        $callables = explode('#', $className);
        $className = trim($callables[0], '\\');
        $className = str_replace('/', '\\', $className);

        if (class_exists($className)) {
            return $className;
        }

        $parts = array_map('ucfirst', array_filter(explode('\\', $className), 'strlen'));
        return implode('\\', $parts);
    }
}
