<?php
error_reporting (E_ALL); 
include ('/config.php'); 
include (SITE_PATH . DS . 'core' . DS . 'core.php'); 
$router = new Router($registry);
$registry->set ('router', $router);
$router->setPath (SITE_PATH . 'controllers');
$router->start();
