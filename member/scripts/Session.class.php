<?php
  session_save_path(dirname(__FILE__).'/../../session');
  ini_set('session.gc_maxlifetime', 24*60*60);

  //セッション管理
  class Session
  {
    private static $instance = null;
    /**
     *
     * @return Session
     */
    public static function getInstance(){
      if (is_null(self::$instance)) {
        self::$instance = new self();
      }
      return self::$instance;
    }

    private function __construct(){
      session_start();
    }

    public function get($key){
      if(isset($_SESSION[$key])){
        return $_SESSION[$key];
      }
      return false;
    }
    public function set($key, $value){
      $_SESSION[$key] = $value;
    }
    public function clear($key){
      if(isset($_SESSION[$key])){
        unset($_SESSION[$key]);
      }
    }
    public function destroy(){
      $_SESSION = array();
      session_destroy();
      self::$instance = null;
    }

    public function dump()
    {
      var_dump($_SESSION);
    }

    public function getCsrfToken($key = 'csrf_token')
    {
      if (empty($_SESSION[$key]) || !is_string($_SESSION[$key])) {
        $_SESSION[$key] = bin2hex(random_bytes(32));
      }
      return $_SESSION[$key];
    }

    public function validateCsrfToken($token, $key = 'csrf_token')
    {
      if (!isset($_SESSION[$key]) || !is_string($token)) {
        return false;
      }
      return hash_equals($_SESSION[$key], $token);
    }
  }