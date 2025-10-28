<?php
  require_once dirname(__FILE__) . '/env.php';
  
  // .envファイルを読み込む
  loadEnv();

  class PdoInterface {
    /**
     *
     * @var PDO
     */
    private $pdo = false;
    private $pdo_statement = null;

    private function __construct(){
      // .envファイルから環境変数を取得
      $db_name = env('DB_NAME');
      $db_addr = env('DB_ADDR');
      $db_user = env('DB_USER');
      $db_pass = env('DB_PASS');

      if (empty($db_name) || empty($db_addr) || empty($db_user)) {
        throw new Exception('データベース接続情報が設定されていません。.envファイルを確認してください。');
      }

      $dsn = "mysql:dbname=" . $db_name . ";host=" . $db_addr;
      $username = $db_user;
      $passwd = $db_pass ? $db_pass : '';

      $options = array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
      );
      try{
        $this->pdo = new PDO($dsn, $username, $passwd, $options);
      }catch(PDOException $e){
        echo $e->getMessage();
      }
    }

    private static $instance = null;

    /**
     *
     * @return PdoInterface
     */
    public static function getInstance(){
      if(is_null(self::$instance)){
        self::$instance = new self();
      }
      return self::$instance;
    }

    public function query($sql, $params = false){
      if($this->pdo === false){
        return false;
      }

      $ret = false;
      if($params === false){
        $ret = $this->pdo_statement = $this->pdo->query($sql);
      }else{
        $this->pdo_statement = $this->pdo->prepare($sql);
        if($this->pdo_statement !== false && is_array($params) && $this->pdo_statement->execute($params)){
          $ret = $this->pdo_statement;
        }
      }
      return $ret;
    }


    public function fetch_assoc(){
      return $this->pdo_statement->fetch(PDO::FETCH_ASSOC);
    }

    public function lastInsertId(){
      return $this->pdo->lastInsertId();
    }

    public function beginTransaction(){
      return $this->pdo->beginTransaction();
    }
    public function commitTransaction(){
      return $this->pdo->commit();
    }
    public function rollbackTransaction(){
      return $this->pdo->rollback();
    }

    public function errorInfo()
    {
      //return $this->pdo->errorInfo();
      return $this->pdo_statement->errorInfo();
    }
  }