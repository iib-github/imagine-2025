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

      // 画像の派生生成（jpg/pngのみ）
      if (in_array(strtolower($ext), array('jpg','jpeg','png'))) {
        $this->generateVariants($img_path);
      }

      return $file_name . "." . $ext;
    }

    private function generateVariants($img_path) {
      // 作成する幅一覧
      $targetWidths = array(320, 640, 1280);
      $pathInfo = pathinfo($img_path);
      $srcExt = strtolower($pathInfo['extension']);

      // GDで読み込み
      if ($srcExt === 'jpg' || $srcExt === 'jpeg') {
        $src = @imagecreatefromjpeg($img_path);
      } elseif ($srcExt === 'png') {
        $src = @imagecreatefrompng($img_path);
      } else {
        return;
      }
      if (!$src) return;

      $srcW = imagesx($src);
      $srcH = imagesy($src);

      foreach ($targetWidths as $w) {
        if ($srcW <= $w) continue; // 元より大きい場合のみ縮小
        $h = intval($srcH * ($w / $srcW));
        $dst = imagecreatetruecolor($w, $h);
        // 透過考慮（PNG）
        if ($srcExt === 'png') {
          imagealphablending($dst, false);
          imagesavealpha($dst, true);
        }
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $srcW, $srcH);

        // JPG派生
        $jpgPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $w . '.jpg';
        @imagejpeg($dst, $jpgPath, 82);
        @chmod($jpgPath, 0666);

        // WebP派生（関数がある場合）
        if (function_exists('imagewebp')) {
          $webpPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $w . '.webp';
          @imagewebp($dst, $webpPath, 80);
          @chmod($webpPath, 0666);
        }

        imagedestroy($dst);
      }

      imagedestroy($src);
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