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
- Cookies habilitados no navegador
- Opcionalmente, compatibilidade com sessão legada via `session_start()`

---

## Instalação

1. Instale ou copie o pacote para o projeto.
2. Gere o autoload do Composer, se necessário:

```bash
composer dump-autoload
```

3. Configure os cookies do roteador no bootstrap da aplicação:

```php
<?php
$csrfToken = bin2hex(random_bytes(32));

setcookie('OLIVIA_APP_NAMESPACE', 'App', 0, '/');
setcookie('OLIVIA_CONTROLLER_FOLDER', 'Controller', 0, '/');
setcookie('OLIVIA_MIDDLEWARE_FOLDER', 'Middleware', 0, '/');
setcookie('OLIVIA_BASE_PATH', '', 0, '/');
setcookie('OLIVIA_CSRF', 'true', 0, '/');
setcookie('OLIVIA_CSRF_TOKEN', $csrfToken, 0, '/', '', true, true);
```

Se você ainda usa a integração antiga, o pacote continua aceitando `$_SESSION['App_folder']`, `$_SESSION['Controller_folder']`, `$_SESSION['Middleware_folder']`, `$_SESSION['BASENAME']`, `$_SESSION['CSRF']` e `$_SESSION['UUID']` como fallback.

Os mesmos valores também podem ser enviados por cookie com os nomes antigos ou com os aliases `OLIVIA_*`. Em chamadas manuais de `execute($requestData)`, você pode informar isso em `COOKIE`.

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
    'COOKIE' => $_COOKIE,
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

Com `OLIVIA_CSRF=true`, o roteador valida automaticamente o campo `_token` nas requisições `POST`, `PUT`, `PATCH` e `DELETE`.

Exemplo em formulário HTML:

```html
<input type="hidden" name="_token" value="<?= $_COOKIE['OLIVIA_CSRF_TOKEN'] ?>">
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

- Configuração centralizada em `RouterConfig`, com prioridade para cookies e fallback para `$_SESSION`
- Origem do contexto isolada em stores dedicados para cookie, sessão e fallback
- Dispatcher trabalhando com `Request` e `Route`
- Compatibilidade com rotas legadas em array
- Resolução de controller e middleware mais robusta
- Suporte a `PUT`, `DELETE` e `PATCH`
- Parsing de URI com suporte a query string no matching
- CSRF com token lido de cookie
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
