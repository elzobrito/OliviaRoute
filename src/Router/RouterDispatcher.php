<?php

namespace OliviaRouter;


class RouterDispatcher
{
    private $route;
    private $clauses;

    public function __construct(array $route, array $clauses)
    {
        $this->route = $route;
        $this->clauses = $clauses;
    }

    public function dispatch($request_data)
    {

        $_SESSION['e404'] = true;
        foreach ($this->route as $actions) {
            if ($request_data['REQUEST_METHOD'] === strtoupper($actions['http_method'])) {
                if (preg_match($actions['url_pattern'], $request_data['REQUEST_URI'], $params) === 1) {
                    $handler = $actions['handler'];

                    if ($actions['middleware'] != null) {
                        $middlewares = is_array($actions['middleware']) ? $actions['middleware'] : [$actions['middleware']];
                        $this->executeMiddlewares($middlewares);
                    }

                    $this->executeHandler($handler, $params, $request_data);
                    $_SESSION['e404'] = false;
                    break;
                }
            }
        }
    }

    private function executeMiddlewares(array $middlewares)
    {
        foreach ($middlewares as $middleware) {
            $callMiddleware = $this->callMiddlewareClass($middleware . '#handle');
            $middlewareInstance = MiddlewareFactory::createMiddleware($callMiddleware['middleware']);
            $middlewareInstance->handle();
        }
    }

    private function executeHandler($handler, $params, $request_data)
    {
        $callHandler = $this->callHandlerClass($handler);
        $handlerInstance = ControllerFactory::createController($callHandler['controller']);
        $format = array_key_exists('CONTENT_TYPE', $request_data) ? $request_data['CONTENT_TYPE'] : 'text/html';
        $params['format'] = explode('/', $format)[1];
        $params['method'] = $request_data['REQUEST_METHOD'];

        $handlerInstance->{$callHandler['action']}($params);
    }

    private function callMiddlewareClass($str)
    {
        $callables = explode('#', $str);
        $controllerParts = array_map('ucfirst', explode('/', $callables[0]));
        $middleware = $_SESSION['App_folder'] . '\\' . $_SESSION['Middleware_folder'] . '\\' . implode('\\', $controllerParts);
        return ['middleware' => $middleware, 'action' => $callables[1]];
    }

    private function callHandlerClass($str)
    {
        $callables = explode('#', $str);
        $controllerParts = array_map('ucfirst', explode('/', $callables[0]));
        $controller = $_SESSION['App_folder'] . '\\' . $_SESSION['Controller_folder'] . '\\' . implode('\\', $controllerParts);
        return ['controller' => $controller, 'action' => $callables[1]];
    }
}
