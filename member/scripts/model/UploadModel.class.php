<?php
  require_once dirname(__FILE__) . '/../BaseModel.class.php';
  require_once dirname(__FILE__) . '/../UploadLib.class.php';

  class UploadModel extends BaseModel {

    // コンテンツの表示、非表示
    const ACTIVE = 1;
    const INACTIVE = 2;

    public function __construct()
    {
      parent::set_table_name('upload_master');
    }


    /**
     * アップロード登録
     *
     * @param array $data コンテンツ情報配列
     * @return 成否（boolean）
     */
    public function registerUploadWork($data) {

      // TODO orderのintチェック、桁数チェック
      // TODO order重複チェック

      if(empty($data['upload_id'])) {
        // insert時
        // insertしたレコードのIDを取得
        parent::insert($data);
        $upload_id = $this->lastInsertId();
      } else {
        // update時
        $upload_id = $data['upload_id'];
      }

      // アップロードファイルのアップロード
      $upload_cont = 'up_' . $upload_id;
      if(($fname = UploadLib::getInstance()->_upload('file', 'upload', $upload_cont)) !== false) {
        $data['path'] = 'contents/upload/' . $fname;
      }

      return parent::update($data, array('upload_id' => $upload_id));
    }


    /**
     * アップロード取得
     *
     * @param int $upload_id アップロードID
     * @return アップロード
     */
    public function UploadById($upload_id) {
      $upload_list = $this->select(array('upload_id '=>$upload_id));
      $upload = $upload_list[0];
      return $upload;
    }


    /**
     * 有効なアップロード取得
     * @param int $num アップロードの取得数 nullの場合は全件
     * @return array アップロードリスト
     */
    public function UploadList($num=null,$member_id) {
      if(!empty($num)) {
        $upload_list = $this->select(array('is_active'=>1,'member_id'=>$member_id), array('note_date'=>self::ORDER_DESC), array(self::LIMIT=>$num));
      } else {
        $upload_list = $this->select(array('is_active'=>1,'member_id'=>$member_id), array('note_date'=>self::ORDER_DESC));
      }
      return $upload_list;
    }


    /**
     * 非公開のカテゴリ一覧取得（バッチ処理用）
     *
     * @return array アップロードリスト
     */
    public function getInActiveUploadList() {
      $upload_list = $this->select(array('indicate_flag'=>2));
      return $upload_list;
    }


  }