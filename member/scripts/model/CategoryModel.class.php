<?php
  require_once dirname(__FILE__) . '/../BaseModel.class.php';
  require_once dirname(__FILE__) . '/../UploadLib.class.php';

  class CategoryModel extends BaseModel {

    // コンテンツの表示、非表示
    const ACTIVE = 1;
    const INACTIVE = 2;

    public function __construct()
    {
      parent::set_table_name('category_master');
    }


    /**
     * カテゴリー登録
     *
     * @param array $data カテゴリー情報配列
     * @return 成否（boolean）
     */
    public function registerCategory($data) {

      // TODO ナンバーのintチェック、桁数チェック
      // TODO ナンバー重複チェック

      if(empty($data['category_id'])) {
        // insert時
        // insertしたレコードのIDを取得
        parent::insert($data);
        $category_id = $this->lastInsertId();
      } else {
        // update時
        $category_id = $data['category_id'];
      }

      // トップバナー画像のアップロード
      $fname_topbnr = 'ctg_' . $category_id . '-' . 'bnr';
      if(($fname = UploadLib::getInstance()->_upload('bnr-img', 'category', $fname_topbnr)) !== false) {
        $data['category_top_img'] = 'contents/category/' . $fname;
      }

      // メイン画像のアップロード
      $fname_main = 'ctg_' . $category_id . '-' . 'main';
      if(($fname = UploadLib::getInstance()->_upload('main-img', 'category', $fname_main)) !== false) {
        $data['category_list_img'] = 'contents/category/' . $fname;
      }

      return parent::update($data, array('category_id' => $category_id));
    }


    /**
     * 非公開のカテゴリ一覧取得（バッチ処理用）
     *
     * @return array カテゴリーリスト
     */
    public function getInActiveCategoryList() {
      $category_list = $this->select(array('indicate_flag'=>2));
      return $category_list;
    }


  }