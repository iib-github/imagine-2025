<?php
  require_once dirname(__FILE__) . '/../BaseModel.class.php';
  require_once dirname(__FILE__) . '/../UploadLib.class.php';

  class ContentModel extends BaseModel {

    // コンテンツの表示、非表示
    const ACTIVE = 1;
    const INACTIVE = 2;

    // FAQコンテンツかどうか
    const IS_FAQ = 1;
    const IS_NOT_FAQ = 2;

    public function __construct()
    {
      parent::set_table_name('content_master');
    }


    /**
     * コンテンツ登録
     *
     * @param array $data コンテンツ情報配列
     * @return 成否（boolean）
     */
    public function registerContent($data) {

      // TODO orderのintチェック、桁数チェック
      // TODO order重複チェック

      if(empty($data['content_id'])) {
        // insert時
        // insertしたレコードのIDを取得
        parent::insert($data);
        $content_id = $this->lastInsertId();
      } else {
        // update時
        $content_id = $data['content_id'];
      }

      // サムネイル画像のアップロード
      $fname_thumbnail = 'cont_' . $content_id . '-' .'thumbnail';
      if(($fname = UploadLib::getInstance()->_upload('thumbnail', 'content', $fname_thumbnail)) !== false) {
        $data['thumbnail_url'] = 'contents/content/' . $fname;
      }

      // 資料のアップロード
      $fname_work = 'cont_' . $content_id . '-' .'work';
      if(($fnames = UploadLib::getInstance()->_upload('txt_url', 'works', $fname_work)) !== false) {
        $data['text_dl_url'] = 'contents/works/' . $fnames;
      }

      // 書き起こし資料のアップロード
      $fname_document = 'cont_' . $content_id . '-' .'document';
      if(($fnames = UploadLib::getInstance()->_upload('document', 'document', $fname_document)) !== false) {
        $data['message_dl_url'] = 'contents/document/' . $fnames;
      }

      return parent::update($data, array('content_id' => $content_id));
    }


    /**
     * 非公開のコンテンツ一覧取得（バッチ処理用）
     *
     * @return array コンテンツリスト
     */
    public function getInActiveContentList() {
      $content_list = $this->select(array('indicate_flag'=>2));
      return $content_list;
    }


  }