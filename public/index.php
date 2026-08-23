<?php
declare(strict_types=1);
session_start();
require dirname(__DIR__).'/app/bootstrap.php';
$app=new App(require dirname(__DIR__).'/config/routes.php');
$app->run($_SERVER['REQUEST_METHOD']??'GET', parse_url($_SERVER['REQUEST_URI']??'/', PHP_URL_PATH)?:'/');
