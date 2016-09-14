<?php
namespace Modules\Auth;

class Sign extends \Libraries\Database\Database {

  private $hash = null;
  private $userData = null;

  function __construct() {
    parent::__construct();
    $this->token = new Token();
  }

  function getUserData() {
    return $this->userData;
  }


  function in($data) {
    $email = (isset($data['email'])) ? $data['email'] : '';
    $password = (isset($data['password'])) ? $data['password'] : '';

    if(empty($email) || empty($password))
      return ['status'=>'error', 'message'=>'E-mail e/ou senha não enviados'];

    $email = $this->db->escape($email);
    $password = $this->db->escape(hash('sha256', $password));

    $user = $this->db->query("SELECT id_user FROM users WHERE email = $email AND password = $password LIMIT 1");
    if($user->rowCount() == 0)
      return ['status'=>'error', 'message'=>'E-mail e/ou senha inválidos'];
    $idUser = $user->result()[0]->id_user;
    $this->hash = $this->token->createHash($idUser);

    return ['status'=>'success'];
  }

  function on() {

    if (!function_exists('apache_request_headers')) {
      function apache_request_headers() {
        foreach($_SERVER as $key=>$value) {
            if (substr($key,0,5)=="HTTP_") {
                $key=str_replace(" ","-",ucwords(strtolower(str_replace("_"," ",substr($key,5)))));
                $out[$key]=$value;
            }else{
              $out[$key]=$value;
            }
        }
        return $out;
      }
    }


    $auth = apache_request_headers();
    $token = (isset($auth['Authorization'])) ? $auth['Authorization'] : '';
    // $token = (isset([])) ? getallheaders()['Authorization'] : '';

    if($this->token->validateHash($token)) {
      $user = $this->token->getUserAuth();
      $this->userData = $this->db->query("SELECT id_user, email, name, store.store_name AS store, store.store_id AS id_store
                                          FROM users
                                            INNER JOIN store
                                              ON store.store_id = users.id_store
                                          WHERE id_user = $user LIMIT 1")->result()[0];
      return true;
    }
    return false;
  }

  function out() {
    $idToken = $this->token->getTokenAuth();
    $idUser = $this->getUserData()->id_user;
    $this->db->query("UPDATE auth_users SET active = 0 WHERE id_user = $idUser AND id_auth = $idToken");
  }
}
