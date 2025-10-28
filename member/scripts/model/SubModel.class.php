<?php
  require_once dirname(__FILE__) . '/../BaseModel.class.php';
  require_once dirname(__FILE__) . '/../UploadLib.class.php';

  class SubModel extends BaseModel {

    // コンテンツの表示、非表示
    const ACTIVE = 1;
    const INACTIVE = 2;

    // FAQコンテンツかどうか
    // const IS_FAQ = 1;
    // const IS_NOT_FAQ = 2;

    public function __construct()
    {
      parent::set_table_name('sub_master');
    }


    /**
     * コンテンツ登録
     *
     * @param array $data コンテンツ情報配列
     * @return 成否（boolean）
     */
    public function registerSub($data) {

      // TODO orderのintチェック、桁数チェック
      // TODO order重複チェック

      if(empty($data['sub_id'])) {
        // insert時
        // insertしたレコードのIDを取得
        parent::insert($data);
        $sub_id = $this->lastInsertId();
      } else {
        // update時
        $sub_id = $data['sub_id'];
      }

      // サムネイル画像のアップロード
      $fname_thumbnail = 'sub_' . $sub_id . '-' .'thumbnail';
      if(($fname = UploadLib::getInstance()->_upload('thumbnail', 'sub', $fname_thumbnail)) !== false) {
        $data['thumbnail_url'] = 'contents/sub/' . $fname;
      }

      // 資料のアップロード
      $sname_sub = 'sub_' . $sub_id . '-' .'cont';
      if(($sname = UploadLib::getInstance()->_upload('content', 'sub', $sname_sub)) !== false) {
        $data['content_url'] = 'contents/sub/' . $sname;
      }

      return parent::update($data, array('sub_id' => $sub_id));
    }


    /**
     * 非公開のコンテンツ一覧取得（バッチ処理用）
     *
     * @return array コンテンツリスト
     */
    public function getInActiveSubList() {
      $sub_list = $this->select(array('indicate_flag'=>2));
      return $sub_list;
    }


  }