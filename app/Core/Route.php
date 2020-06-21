<?php namespace App\Core;

class Route
{
    private static $routes = [];

    public static function start()
    {
        $request = new Request();

        $currentRoute = explode('?', $_SERVER['REQUEST_URI'])[0];

        $isFoundRoute = false;
        foreach (self::$routes as $pattern => $route) {
            preg_match($pattern, $currentRoute, $matches);
            if (!empty($matches)) {
                $isFoundRoute = true;
                break;
            }
        }

        if ($isFoundRoute) {
            $controller = $route['class'];
            $action = $route['action'];
            unset($matches[0]);
        } else {
            header('Content-type: application/json');
            http_response_code(400);
            echo json_encode(['errors' => ['Method not found']]);
            die;
        }

        if (in_array($_SERVER['REQUEST_METHOD'], $route['method'])) {
            $controller = new $controller;
            if (method_exists($controller, $action)) {
                $response = $controller->$action($request);
                echo json_encode($response);
            }
        } else {
            http_response_code(400);
            header('Content-type: application/json');
            echo json_encode([
                'errors' => ['This route doesn\'t support ' . $_SERVER['REQUEST_METHOD'] .
                    '. Use ' . implode('/', $route['method'])]]);
            die;
        }
    }

    static private function add(string $route, string $class, string $action, array $methods)
    {
        $params = [
            'class' => $class,
            'action' => $action,
            'method' => $methods,
        ];
        self::$routes["~^\\{$route}$~"] = $params;
    }

    static function post(string $route, string $class, string $action)
    {
        self::add($route, $class, $action, ['POST']);
    }
}
