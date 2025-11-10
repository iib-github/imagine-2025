<?php
  require_once dirname(__FILE__) . '/../BaseModel.class.php';


  class ContactModel extends BaseModel {

    public function __construct() {
      parent::set_table_name('contact_master');
    }


    /**
     * コンテンツ登録
     *
     * @param array $data コンテンツ情報配列
     * @return 成否（boolean）
     */
    public function registerContact($data) {

      // TODO orderのintチェック、桁数チェック
      // TODO order重複チェック

      if(empty($data['id'])) {
        // insert時
        parent::insert($data);
        return true;
      }

      // update時
      $contact_id = $data['id'];
      return parent::update($data, array('id' => $contact_id));
    }


    /**
     * お問い合わせ取得
     *
     * @param int $id お問い合わせID
     * @return ニュース
     */
    public function getContactById($id = NULL) {
      $contact_list = $this->select(array('id'=>$id));
      $contact = '';
      if(!empty($contact_list)) {
        $contact = $contact_list[0];
      }
      return $contact;
    }


    /**
     * 有効なお問い合わせ取得
     * @return array お問い合わせリスト
     */
    public function getContactList() {
      $contact_list = $this->select(array('delete_flag'=>1), array('created_date'=>self::ORDER_DESC));
      return $contact_list;
    }

  }