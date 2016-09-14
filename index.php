<?php
include '__config.php';

function logon() {
  global $sign;
  if(!$sign->on()) {
    json(["message"=>"Autenticação inválida"], 401);
    exit;
  }
}

$Router->when("/login", function() use ($sign) {
  $valid = $sign->in($_POST);
  if($valid['status'] == 'success')
    json(["token"=>$sign->getHash()]);
  else
    json(["message"=>$valid['message']], 412);
});

$Router->when("/logout", function() use ($sign) {
  logon();
  $sign->out();
});

$Router->when("/me", function() use ($sign) {
  logon();
  json(["status"=>"success", "data"=>$sign->getUserData()]);
});

$Router->when("/::", function() {
  json([], 404);
});
