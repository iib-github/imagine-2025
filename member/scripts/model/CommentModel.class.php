<?php
  require_once dirname(__FILE__) . '/../BaseModel.class.php';


  class CommentModel extends BaseModel {

    public function __construct() {
      parent::set_table_name('comment_master');
    }


    /**
     * コメント登録
     *
     * @param array $data コンテンツ情報配列
     * @return 成否（boolean）
     */
    public function registerComment($data) {

      // TODO orderのintチェック、桁数チェック
      // TODO order重複チェック

      if(empty($data['comment_id'])) {
        // insert時
        // insertしたレコードのIDを取得
        parent::insert($data);
        $comment_id = $this->lastInsertId();
      } else {
        // update時
        $comment_id = $data['comment_id'];
      }

      return parent::update($data, array('comment_id' => $comment_id));
    }


    /**
     * コメント取得
     *
     * @param int $id お問い合わせID
     * @return コメント
     */
    public function getCommentById($id = NULL) {
      $comment_list = $this->select(array('id'=>$id));
      $comment = '';
      if(!empty($comment_list)) {
        $comment = $comment_list[0];
      }
      return $comment;
    }


    /**
     * 有効なコメント取得
     * @return array お問い合わせリスト
     */
    public function getCommentList() {
      $comment_list = $this->select(array('delete_flag'=>1), array('created_date'=>self::ORDER_DESC));
      return $comment_list;
    }

  }