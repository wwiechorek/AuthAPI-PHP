<?php
namespace Modules\Auth;

class Token extends \Libraries\Database\Database {

  private $tokenAuth = null;
  private $userAuth = null;

  // -- não acho que é necessário
  // function deleteToken($idToken) {
  //   if(empty($this->userAuth)) return false;
  //   $idToken = intval($idToken);
  //   $user = intval($this->userAuth);
  //   $this->db->query("UPDATE auth_users SET active = 0 WHERE id_user = $user AND id_auth = $idToken");
  // }
  //
  // function clearTokens() {
  //   if(empty($this->userAuth)) return false;
  //   $user = intval($this->userAuth);
  //   $this->db->query("UPDATE auth_users SET active = 0 WHERE id_user = $user");
  // }

  function getUserAuth() {
    return $this->userAuth;
  }

  function getTokenAuth() {
    return $this->tokenAuth;
  }

  function createHash($idUser) {
    $idUser = intval($idUser);
    $host = $this->db->escape((isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : '');
    // $user_agent = $this->db->escape((isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '');
    $ip = $this->db->escape((isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '');

    $token = hash('sha256', uniqid($idUser, true));

    //cria um uuid unico
    $uuid = sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
    //adiciona um uniq pra ser mais único
    $uuid = $uuid.uniqid();


    $this->db->query("INSERT INTO auth_users (id_user, uuid, token, host, ip, `date`)
                                      VALUES ($idUser, '$uuid', '$token', $host, $ip, CURRENT_TIME)");

    $hash = json_encode(['uuid'=>$uuid, 'auth'=>$token]);
    $hash = base64_encode($hash);
    return $hash;
  }



  function validateHash($hash = '') {
    if(empty($hash)) return false;

    $hash = base64_decode($hash);
    $hash = (Array) json_decode($hash);
    if(!is_array($hash)) return false;

    // print_r($hash); exit;

    $host = $this->db->escape((isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : '');
    // $user_agent = $this->db->escape((isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '');

    $authorization = (isset($hash['auth'])) ? $hash['auth'] : '';
    $uuid = (isset($hash['uuid'])) ? $hash['uuid'] : '';

    if(empty($authorization) || empty($uuid)) return false;

    $authorization = $this->db->escape($authorization);
    $uuid = $this->db->escape($uuid);

    $auth = $this->db->query("SELECT id_auth, id_user FROM auth_users
                              WHERE token = $authorization
                              AND active = 1
                              AND uuid = $uuid
                              AND (host = $host || host = '*')
                              -- AND (user_agent = $user_agent || user_agent = '*')
                              LIMIT 1
                              ");

    if($auth->rowCount() == 0) return false;
    $auth = $auth->result()[0];
    $this->userAuth = $auth->id_user;
    $this->tokenAuth = $auth->id_auth;
    return true;
  }
}
