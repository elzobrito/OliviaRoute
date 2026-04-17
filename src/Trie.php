<?php

namespace OliviaRouter;

class Trie
{
    public function search(string $pattern, string $uri)
    {
        return $this->searchRegex($this->patternToRegex($pattern), $uri);
    }

    public function searchRegex(string $regex, string $uri)
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $subject = is_string($path) && $path !== '' ? $path : $uri;

        if (preg_match($regex, $subject, $matches) === 1) {
            return $matches;
        }

        return false;
    }

    private function patternToRegex(string $pattern): string
    {
        $pattern = '/' . ltrim($pattern, '/');
        $pattern = preg_quote($pattern, '/');
        $pattern = preg_replace('/\\\{([a-zA-Z0-9_]+)\\\}/', '(?P<$1>[^\\/]+)', $pattern);
        return '/^' . $pattern . '$/';
    }
}
