<?php

class Router {

    private $routes = [];

    function setRoutes(Array $routes) {
        $this->routes = $routes;
    }

	// loops through routes to see if any route is contained in the $url
	//	if so, return that file name
    function getFilename(string $url) {
        foreach($this->routes as $route => $file) {
            if(strpos($url, $route) !== false){
                return $file;
            }
        }
    }
}

?>