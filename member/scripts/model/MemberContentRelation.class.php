<?php
  require_once dirname(__FILE__) . '/../BaseModel.class.php';


  class MemberContentRelation extends BaseModel {


    public function __construct($member_id = NULL) {
      parent::set_table_name('member_content_relation');
    }


    /**
     * 達成済みコンテンツリストを返す。
     * @param int $member_id メンバーID
     * @param int $category_id カテゴリーID
     * @return array：達成済みコンテンツリスト
     */
    public function getCompList($member_id, $category_id) {
      $comp_list = parent::select(array('member_id'=>$member_id, 'category_id'=>$category_id));
      return $comp_list;
    }


  }