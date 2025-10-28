<?php
  require_once dirname(__FILE__) . '/PdoInterface.class.php';

  /**
   *
   * モデル基本クラス
   * シンプルなクエリのみサポートしている
   * 結合クエリや複雑なクエリは派生クラスで実装する
   */
  abstract class BaseModel
  {
    //検索条件定数
    const IS_NULL     = 'is_null';
    const IS_NOT_NULL = 'is_not_null';

    //更新定数
    const NOW         = 'now';

    //ソート定数
    const ORDER_ASC   = 'asc';
    const ORDER_DESC  = 'desc';

    //リミット定数
    const LIMIT       = 'limit';
    const OFFSET      = 'offset';

    //対象テーブル名の定義
    private $table_name = '';
    protected function set_table_name($table_name)
    {
      $this->table_name = $table_name;
    }

    /**
     * レコード取得
     *
     * @param array $where_data 抽出条件、nullで全件
     * @param array $order_data ソート条件、nullで指定無し
     * @param array $limit_data リミット条件、nullで全件
     * @return レコード配列
     */
    public function select($where_data=null, $order_data=null, $limit_data=null)
    {
      $pdo = PdoInterface::getInstance();

      //取得条件SQLの生成
      $where = '';
      $param = false;
      if(!empty($where_data)){
        $param = array();
        foreach($where_data as $k => $v)
        {
          if($v === BaseModel::IS_NULL){
            $where .= "AND {$k} IS NULL ";
          }elseif($v === BaseModel::IS_NOT_NULL){
            $where .= "AND {$k} IS NOT NULL ";
          }else{
            $where .= "AND {$k}=? ";
            $param[] = $v;
          }
        }
        $where = " WHERE " . trim($where, "AND");
      }

      //ソート条件SQLの生成
      $order = '';
      if(!empty($order_data)){
        foreach($order_data as $k => $v)
        {
          if($v === BaseModel::ORDER_ASC){
            $order .= ",{$k} ASC";
          }elseif($v === BaseModel::ORDER_DESC){
            $order .= ",{$k} DESC";
          }
        }
        $order = " ORDER BY " . trim($order, ",");
      }

      //リミット条件SQLの生成
      $limit = '';
      if(!empty($limit_data)){
        $limit_val = '';
        $offset_val = '';
        foreach($limit_data as $k => $v)
        {
          if($k === BaseModel::LIMIT){
            $limit_val = $v;
          }elseif($k === BaseModel::OFFSET){
            $offset_val = $v;
          }
        }
        if($limit_val !== '' && $offset_val !== ''){
          $limit = " LIMIT {$offset_val},{$limit_val}";
        }elseif($limit_val !== '' && $offset_val === ''){
          $limit = " LIMIT {$limit_val}";
        }elseif($limit_val === '' && $offset_val !== ''){
          //OFFSETのみの指定はLIMIT句を生成しない
          $limit = "";
        }
      }

      //クエリ発行
      $pdo->query("SELECT * FROM " . $this->table_name . " {$where} {$order} {$limit}", $param);

      $result = array();
      while($rs = $pdo->fetch_assoc())
      {
        $result[] = $rs;
      }
      return $result;
    }

    /**
     * レコード更新
     *
     * @param array $update_data 更新データ
     * @param array $where_data 更新対象条件、nullで全件指定
     *
     * @return 失敗はfalse、成功はそれ以外を返す
     */
    public function update($update_data, $where_data = null)
    {
      $pdo = PdoInterface::getInstance();

      // 更新日に現在の日時を登録するためのパラメーターを追加。
      $date = new DateTime();
      $update_data['modified_date'] = $date->format('Y-m-d H:i:s');

      //更新SQLの生成
      $fields = '';
      $param = array();
      foreach($update_data as $k => $v)
      {
        if($v === BaseModel::IS_NULL){
          $fields .= ", {$k}=NULL ";
        }elseif($v === BaseModel::NOW){
          $fields .= ", {$k}=NOW() ";
        }else{
          $fields .= ", {$k}=? ";
          $param[] = $v;
        }
      }
      $fields = trim($fields, ",");

      //対象条件SQLの生成
      $where = '';
      if(!empty($where_data))
      {
        foreach($where_data as $k => $v)
        {
          if($v === BaseModel::IS_NULL){
            $where .= "AND {$k} IS NULL ";
          }elseif($v === BaseModel::IS_NOT_NULL){
            $where .= "AND {$k} IS NOT NULL ";
          }else{
            $where .= "AND {$k}=? ";
            $param[] = $v;
          }
        }
        $where = "WHERE " . trim($where, "AND");
      }
      return $pdo->query("UPDATE " . $this->table_name . " SET {$fields} {$where}", $param);
    }

    /**
     * レコード追加
     *
     * @param array $insert_data 追加データ
     *
     * @return 失敗はfalse、成功はそれ以外を返す
     */
    public function insert($insert_data)
    {
      $pdo = PdoInterface::getInstance();

      //追加SQLの生成
      $fields = '';
      $placeholder = '';
      $param = array();
      foreach($insert_data as $k => $v)
      {
        $fields .= ",{$k}";
        if($v === BaseModel::IS_NULL){
          $placeholder .= ",NULL";
        }elseif($v === BaseModel::NOW){
          $placeholder .= ",NOW()";
        }else{
          $placeholder .= ",?";
          $param[] = $v;
        }
      }
      $fields = trim($fields, ",");
      $placeholder = trim($placeholder, ",");

      // 作成日と更新日に現在の日時を登録する。
      $fields .= ",created_date,modified_date";
      $placeholder .= ",?,?";
      $date = new DateTime();
      $param[] = $date->format('Y-m-d H:i:s');
      $param[] = $date->format('Y-m-d H:i:s');

      return $pdo->query("INSERT INTO " . $this->table_name . " ({$fields}) VALUES ({$placeholder})", $param);
    }

    /**
     * レコード上書き
     *
     * @param array $replace_data 上書きデータ
     *
     * @return 失敗はfalse、成功はそれ以外を返す
     */
    public function replace($replace_data)
    {
      $pdo = PdoInterface::getInstance();

      //追加SQLの生成
      $fields = '';
      $placeholder = '';
      $param = array();
      foreach($replace_data as $k => $v)
      {
        $fields .= ",{$k}";
        if($v === BaseModel::IS_NULL){
          $placeholder .= ",NULL";
        }elseif($v === BaseModel::NOW){
          $now = date("Y-m-d H:i:s");
          $placeholder .= ",'{$now}'";
        }else{
          $placeholder .= ",?";
          $param[] = $v;
        }
      }
      $fields = trim($fields, ",");
      $placeholder = trim($placeholder, ",");

      return $pdo->query("REPLACE INTO " . $this->table_name . " ({$fields}) VALUES ({$placeholder})", $param);
    }

    /**
     * レコード削除
     *
     * @param array $where_data 削除対象条件、nullで全件指定
     *
     * @return 失敗はfalse、成功はそれ以外を返す
     */
    public function delete($where_data = null)
    {
      $pdo = PdoInterface::getInstance();

      //対象条件SQLの生成
      $where = '';
      $param = false;
      if(!empty($where_data))
      {
        $param = array();
        foreach($where_data as $k => $v)
        {
          if($v === BaseModel::IS_NULL){
            $where .= "AND {$k} IS NULL ";
          }elseif($v === BaseModel::IS_NOT_NULL){
            $where .= "AND {$k} IS NOT NULL ";
          }else{
            $where .= "AND {$k}=? ";
            $param[] = $v;
          }
        }
        $where = "WHERE " . trim($where, "AND");
      }

      return $pdo->query("DELETE FROM " . $this->table_name . " {$where}", $param);
    }

    /**
     * レコード件数の取得
     *
     * @return レコード件数を返す
     */
    public function count($where_data = null)
    {
      $pdo = PdoInterface::getInstance();

      //対象条件SQLの生成
      $where = '';
      $param = false;
      if(!empty($where_data))
      {
        $param = array();
        foreach($where_data as $k => $v)
        {
          if($v === BaseModel::IS_NULL){
            $where .= "AND {$k} IS NULL ";
          }elseif($v === BaseModel::IS_NOT_NULL){
            $where .= "AND {$k} IS NOT NULL ";
          }else{
            $where .= "AND {$k}=? ";
            $param[] = $v;
          }
        }
        $where = "WHERE " . trim($where, "AND");
      }

      if(empty($where)){
        $pdo->query("SELECT COUNT(*) AS cnt FROM " . $this->table_name . " {$where}");
      }else{
        $pdo->query("SELECT COUNT(*) AS cnt FROM " . $this->table_name . " {$where}", $param);
      }
      $ret = $pdo->fetch_assoc();
      return $ret['cnt'];
    }

    public function lastInsertId()
    {
      $pdo = PdoInterface::getInstance();
      return $pdo->lastInsertId();
    }

    public function errorInfo()
    {
      $pdo = PdoInterface::getInstance();
      return $pdo->errorInfo();
    }
  }