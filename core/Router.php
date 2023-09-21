<?php

class Router
{
    private $routes = [];
    function setRoutes($routes)
    {
        $this->routes = $routes;
    }
    function getFilename($url)
    {
        foreach ($this->routes as $route => $file) {

            if (strpos($url, $route) !== false) {
                return $file;
            }
        }
    }
}
