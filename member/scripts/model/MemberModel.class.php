<?php
  require_once dirname(__FILE__) . '/../BaseModel.class.php';
  require_once dirname(__FILE__) . '/../model/CategoryModel.class.php';
  require_once dirname(__FILE__) . '/../model/ContentModel.class.php';
  require_once dirname(__FILE__) . '/../model/UploadModel.class.php';
  require_once dirname(__FILE__) . '/../model/MemberContentRelation.class.php';


  class MemberModel extends BaseModel {


    public function __construct($member_id = NULL) {
      parent::set_table_name('member_master');
    }


    /**
     * メールアドレスからメンバー取得
     *
     * @param string $login_mail メールアドレス
     * @return メンバー
     */
    public function getMemberByMail($mail) {
      $member_list = parent::select(array('login_mail'=>$mail));
      if (!empty($member_list)) {
        $member = $member_list[0];
      }else{
        $member = NULL;
      }
      return $member;
    }


    /**
     * ログインの成否を返す。
     *
     * @param string $login_mail メールアドレス
     * @param string $password パスワード
     * @return boolean
     */
    public function login($input_mail, $input_pass) {
      $member = $this->getMemberByMail($input_mail);
      $pass = $member["login_password"];
      $now = time();//現在の時間
      $stop_date = strtotime($member["stop_date"]);//退会時間
      if($stop_date) {
        if ($stop_date < $now) {
          return false;
        }
      }
      if($input_pass == $pass) {
        return true;
      } else {
        return false;
      }
    }


    /**
     * 課題（カテゴリー）の達成率を計算し返す。
     *
     * @param int $member_id 会員ID
     * @param int $category_id カテゴリーID
     * @return 達成率：整数
     */
    public function getScore($member_id, $category_id) {

      // 指定の課題（カテゴリー）のコンテンツ数を取得
      $category_model = new CategoryModel();
      $category = $category_model->select(array('category_id'=>$category_id));
      $category = $category[0];
      $number_of_contents = $category['number_of_contents'];

      // 指定の課題（カテゴリー）に紐づく達成済みコンテンツの数を取得
      $member_content_relation = new MemberContentRelation();
      $complete_list = $member_content_relation->getCompList($member_id, $category_id);
      $complete_number = count($complete_list);

      // 達成率を計算する。
      if($number_of_contents == 0) {
        // コンテンツの設定値が0だった場合100を返す
        return 100;
      } else {
        // 達成率（％）は小数点を四捨五入し整数とする。
        $score = (int)round($complete_number / $number_of_contents * 100);
        return $score;
      }

    }


  }