<?php
  class PdoInterface {
    const DB_NAME = "_the_imagine2020";
    const DB_ADDR = "mysql015.phy.heteml.lan";
    const DB_USER = "_the_imagine2020";
    const DB_PASS = "doinatsumi";

    /**
     *
     * @var PDO
     */
    private $pdo = false;
    private $pdo_statement = null;

    private function __construct(){

      $dsn = "mysql:dbname=" . PdoInterface::DB_NAME . ";host=" . PdoInterface::DB_ADDR;
      $username = PdoInterface::DB_USER;
      $passwd   = PdoInterface::DB_PASS;

      //ローカル環境用
      // if($_SERVER['SERVER_NAME'] == 'datsumo.localhost'){
      //   $dsn = "mysql:dbname=datsumo_db;host=127.0.0.1";
      //   $username = "cheki_user";
      //   $passwd   = "cheki_pw";
      // }

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