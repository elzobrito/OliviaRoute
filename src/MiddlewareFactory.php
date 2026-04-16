<?php

namespace OliviaRouter;

class MiddlewareFactory
{
    public static function createMiddleware(string $middlewareName): object
    {
        $controllerClass = self::normalizeClassName($middlewareName);

        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Middleware {$controllerClass} não encontrado.");
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
