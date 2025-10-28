<?php
  //ファイルアップロードクラス
  //contentsディレクトリ以下に保存
  class UploadLib
  {
    private static $instance = null;
    /**
     *
     * @return UploadLib
     */
    public static function getInstance(){
      if(is_null(self::$instance)){
        self::$instance = new self();
      }
      return self::$instance;
    }

    //ファイルアップロード
    //$target_dir = contentsディレクトリ以下のディレクトリ名
    public function upload($image_key, $target_dir, $prefix)
    {
      //保存ファイル名の自動生成
      $file_name = $prefix . "_" . time();

      return $this->_upload($image_key, $target_dir, $file_name);
    }

    //ファイルアップロード
    //$target_dir = contentsディレクトリ以下のディレクトリ名
    public function _upload($image_key, $target_dir, $file_name) {
      if(!isset($_FILES[$image_key]))return false;

      $img_name = $_FILES[$image_key]["name"];
      $img_size = $_FILES[$image_key]["size"];
      $img_type = $_FILES[$image_key]["type"];
      $img_tmp = $_FILES[$image_key]["tmp_name"];

      /* 無制限
       if($img_size > 500000)
       {
      //サイズが大きい
      return false;
      }
      */

      $ext = $this->GetExt($img_name);
      if(strcasecmp($ext, 'png') != 0 && strcasecmp($ext, 'jpg') != 0 && strcasecmp($ext, 'gif') != 0 && strcasecmp($ext, 'pdf') != 0 && strcasecmp($ext, 'txt') != 0)
      {
        //拡張子が違う
        return false;
      }

      $img_dir = $this->getRootPath() . '/' . $target_dir . '/';
      $img_path = $img_dir . $file_name . "." . $ext;

      if(@move_uploaded_file($img_tmp, $img_path) === false){
        return false;
      }
      if(is_file($img_path)){
        @chmod($img_path, 0666);
      }

      return $file_name . "." . $ext;
    }

    private function getRootPath()
    {
      $root_path = dirname(__FILE__) . '/../contents/';
      $root_path = realpath($root_path);
      return $root_path;
    }

    private function GetExt($FilePath){
      $f=strrev($FilePath);
      $ext=substr($f,0,strpos($f,"."));
      return strrev($ext);
    }


  //ファイルアップロード
    //$target_dir = contentsディレクトリ以下のディレクトリ名
    public function upload_work($image_key, $target_dir, $prefix)
    {
      //保存ファイル名の自動生成
      $file_name = $prefix . "_" . time();

      return $this->_upload_work($image_key, $target_dir, $file_name);
    }

    //ファイルアップロード
    //$target_dir = contentsディレクトリ以下のディレクトリ名
    public function _upload_work($image_key, $target_dir, $file_name) {
      if(!isset($_FILES[$image_key]))return false;

      $img_name = $_FILES[$image_key]["name"];
      $img_size = $_FILES[$image_key]["size"];
      $img_type = $_FILES[$image_key]["type"];
      $img_tmp = $_FILES[$image_key]["tmp_name"];

      /* 無制限
       if($img_size > 500000)
       {
      //サイズが大きい
      return false;
      }
      */

      $ext = $this->GetExt($img_name);
      if(strcasecmp($ext, 'png') != 0 && strcasecmp($ext, 'jpg') != 0 && strcasecmp($ext, 'gif') != 0 && strcasecmp($ext, 'pdf') != 0 && strcasecmp($ext, 'txt') != 0)
      {
        //拡張子が違う
        return false;
      }

      $img_dir = $this->getRootPathWork() . '/' . $target_dir . '/';
      $img_path = $img_dir . $file_name . "." . $ext;

      if(@move_uploaded_file($img_tmp, $img_path) === false){
        return false;
      }
      if(is_file($img_path)){
        @chmod($img_path, 0666);
      }

      return $file_name . "." . $ext;
    }

    private function getRootPathWork()
    {
      $root_path = dirname(__FILE__) . '/../works/';
      $root_path = realpath($root_path);
      return $root_path;
    }

  }