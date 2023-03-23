<?php

// BOOTSTRAP: LOADS ALL COMMON CODE FOR API

// load require files
require __DIR__.'/../config.php';
require __DIR__.'/DB.php';
require __DIR__.'/Router.php';
require __DIR__.'/../routes.php';


// instantiate new router
$router = new Router;
$router->setRoutes($routes);

// get request URI
//	& pass it to router object
//	to redirect to correct resource
$url = $_SERVER['REQUEST_URI'];
require __DIR__."/../api/".$router->getFilename($url);
//echo __DIR__."/../api/".$router->getFilename($url);

//echo $router->getFilename($url);
//echo $url;
?>