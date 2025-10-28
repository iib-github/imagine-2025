<?php
  require_once dirname(__FILE__) . '/../BaseModel.class.php';


  class NewsModel extends BaseModel {

    // コンテンツの表示、非表示
    const ACTIVE = 1;
    const INACTIVE = 2;

    public function __construct() {
      parent::set_table_name('news_master');
    }


    /**
     * ニュース取得
     *
     * @param int $nid ニュースID
     * @return ニュース
     */
    public function getNewsById($nid = NULL) {
      $news_list = $this->select(array('id'=>$nid));
      $news = '';
      if(!empty($news_list)) {
        $news = $news_list[0];
      }
      return $news;
    }


    /**
     * 有効なニュース取得
     * @param int $num ニュースの取得数 nullの場合は全件
     * @return array ニュースリスト
     */
    public function getNewsList($num = NULL) {
      if(!empty($num)) {
        $news_list = $this->select(array('is_active'=>1), array('note_date'=>self::ORDER_DESC), array(self::LIMIT=>$num));
      } else {
        $news_list = $this->select(array('is_active'=>1), array('note_date'=>self::ORDER_DESC));
      }
      return $news_list;
    }


    /**
     * 非公開のニュース取得（バッチ処理用）
     * @return array ニュースリスト
     */
    public function getInActiveNewsList() {
      $news_list = $this->select(array('is_active'=>0), array('note_date'=>self::ORDER_DESC));
      return $news_list;
    }

  }