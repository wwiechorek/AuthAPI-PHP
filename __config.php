<?php
spl_autoload_extensions('.php');
spl_autoload_register(function ($class) {
    require_once(str_replace('\\', '/', $class . '.php'));
});

use Libraries\Database\Database;
use Libraries\Router\Router;

$url_path = $_SERVER['QUERY_STRING'];
$url_path = explode("&", $url_path);
$url_path = $url_path[0];

$Router = new Router();
$Router->setRouting($url_path);

Database::newConnection([
  "dbname" => 'authAPI',
  'error' => function($err) {
    echo $err; exit;
  }
]);

$sign = new Modules\Auth\Sign();

//liberando acesso a todos - External access
header('Access-Control-Allow-Origin: *');

function json($data, $code = 200){
  header("Content-Type: application/json; charset=UTF-8",true,$code);
	echo json_encode($data);
}
