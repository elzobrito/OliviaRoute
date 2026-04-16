# OliviaRouter

**Um roteador PHP simples, leve e performático** inspirado em frameworks modernos como Laravel e Symfony.

Mantém compatibilidade com a versão original, mas com melhorias importantes: CSRF validado no dispatcher, código mais organizado, suporte a `PUT`/`DELETE`/`PATCH` e núcleo unificado com `Request`, `Route`, `RouterConfig` e `Trie`.

---

## Recursos

- Rotas por método HTTP: `GET`, `POST`, `PUT`, `DELETE`, `PATCH`
- Parâmetros dinâmicos como `{id}` e `{slug}`
- Middlewares por rota via chaining
- Proteção CSRF opcional
- Factories para controllers e middlewares
- Matching centralizado com `Trie`
- Compatibilidade com o fluxo legado baseado em `execute($request_data)`

---

## Requisitos

- PHP 7.4 ou superior
- Composer autoload configurado
- Sessão ativa com `session_start()`

---

## Instalação

1. Instale ou copie o pacote para o projeto.
2. Gere o autoload do Composer, se necessário:

```bash
composer dump-autoload
```

3. Configure as variáveis de sessão no bootstrap da aplicação:

```php
<?php
session_start();

$_SESSION['App_folder'] = 'App';
$_SESSION['Controller_folder'] = 'Controller';
$_SESSION['Middleware_folder'] = 'Middleware';
$_SESSION['BASENAME'] = '';
$_SESSION['CSRF'] = true;
$_SESSION['UUID'] = bin2hex(random_bytes(32));
```

---

## Uso Básico

```php
<?php

require __DIR__ . '/vendor/autoload.php';

$router = new OliviaRouter\Router();

$router->get('/', 'home#index');
$router->post('/users', 'user#store');
$router->put('/users/{id}', 'user#update');
$router->delete('/users/{id}', 'user#destroy');

$router->middleware('auth')->get('/dashboard', 'dashboard#index');

$requestData = [
    'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
    'REQUEST_URI' => $_SERVER['REQUEST_URI'],
    'POST' => $_POST,
    'GET' => $_GET,
    'CONTENT_TYPE' => $_SERVER['CONTENT_TYPE'] ?? null,
];

$router->execute($requestData);
```

O formato legado continua aceito:

- Controller e middleware podem ser passados como `users#show`, `AuthMiddleware`, `auth` ou namespace completo.
- `execute()` ainda aceita o array tradicional com dados da requisição.

---

## Middlewares

Crie middlewares em `App\Middleware\` com um método `handle()`:

```php
<?php

namespace App\Middleware;

use OliviaRouter\RequestHandler;

class Auth implements RequestHandler
{
    public function handle()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
    }
}
```

Uso:

```php
$router->middleware('auth')->get('/dashboard', 'dashboard#index');
```

---

## Proteção CSRF

Com `$_SESSION['CSRF'] = true`, o roteador valida automaticamente o campo `_token` nas requisições `POST`.

Exemplo em formulário HTML:

```html
<input type="hidden" name="_token" value="<?= $_SESSION['UUID'] ?>">
```

Se o token estiver ausente ou inválido, uma `RuntimeException` é lançada.

---

## Uso com Objetos

Também é possível usar diretamente as classes do núcleo refatorado:

```php
<?php

use OliviaRouter\Request;
use OliviaRouter\Route;
use OliviaRouter\Trie;

$request = new Request(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI'],
    $_POST,
    $_GET,
    $_SERVER,
    $_SERVER['CONTENT_TYPE'] ?? null
);

$trie = new Trie();
$route = new Route('GET', '/users/{id}', 'user#show');

if ($route->matches($request, $trie)) {
    $params = $route->getParams();
}
```

---

## Melhorias da Refatoração

- Configuração centralizada em `RouterConfig`, com fallback compatível para `$_SESSION`
- Dispatcher trabalhando com `Request` e `Route`
- Compatibilidade com rotas legadas em array
- Resolução de controller e middleware mais robusta
- Suporte a `PUT`, `DELETE` e `PATCH`
- Parsing de URI com suporte a query string no matching
- Erros mais claros para classes e métodos inexistentes

---

## Estrutura Atual

```text
OliviaRoute/
├── src/
│   ├── ControllerFactory.php
│   ├── MiddlewareFactory.php
│   ├── Request.php
│   ├── RequestHandler.php
│   ├── Route.php
│   ├── Router.php
│   ├── RouterConfig.php
│   ├── RouterDispatcher.php
│   └── Trie.php
├── vendor/
├── composer.json
└── README.md
```

---

**OliviaRouter**: simples, direto e compatível com código legado.
