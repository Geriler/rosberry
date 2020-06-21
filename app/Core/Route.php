<?php namespace App\Core;

use App\Controllers\ApiController;

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

        $controller = new $controller;
        if (method_exists($controller, $action)) {
            $response = $controller->$action($request);
            echo json_encode($response);
        }
    }

    static function add(string $route, string $class, string $action)
    {
        $params = [
            'class' => $class,
            'action' => $action,
        ];
        self::$routes["~^\\{$route}$~"] = $params;
    }
}
