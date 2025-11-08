<?php
  require_once dirname(__FILE__) . '/../BaseModel.class.php';
  require_once dirname(__FILE__) . '/../UploadLib.class.php';
  require_once dirname(__FILE__) . '/ContentModel.class.php';

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

      if(!isset($data['use_week_flag'])) {
        $data['use_week_flag'] = 1;
      } else {
        $data['use_week_flag'] = (int)$data['use_week_flag'];
      }

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

      $result = parent::update($data, array('category_id' => $category_id));

      // 公開中コンテンツ数を自動集計し反映
      $content_model = new ContentModel();
      $content_count = $content_model->count(array(
        'category_id' => $category_id,
        'indicate_flag' => ContentModel::ACTIVE
      ));
      parent::update(array('number_of_contents' => $content_count), array('category_id' => $category_id));

      return $result;
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

    /**
     * カテゴリIDからカテゴリナンバーを取得
     *
     * @param int $category_id カテゴリID
     * @return string カテゴリナンバー、またはnull
     */
    public function getCategoryNumber($category_id) {
      $category = $this->select(array('category_id' => $category_id));
      return !empty($category) ? $category[0]['category_number'] : null;
    }

    /**
     * 会員のコースに基づいてカテゴリー一覧を取得
     *
     * @param string $course_filter コースフィルタ
     * @param array $order_data ソート条件
     * @return array カテゴリー一覧
     */
    public function getCategoriesByCourse($course_filter, $order_data = array('category_number' => BaseModel::ORDER_ASC)) {
      $where_conditions = array('indicate_flag' => self::ACTIVE);
      
      // コースフィルタを適用
      if ($course_filter !== 'all') {
        $where_conditions['target_course'] = $course_filter;
      }
      
      return $this->select($where_conditions, $order_data);
    }

    /**
     * コース別フィルタリング（PHP側）
     *
     * @param array $categories カテゴリー一覧
     * @param string $course_filter コースフィルタ
     * @return array フィルタリング後のカテゴリー一覧
     */
    public function filterCategoriesByCourse($categories, $course_filter) {
      $filtered_categories = array();
      
      foreach ($categories as $category) {
        // target_courseが'all'またはNULLの場合は常に表示（旧来のコンテンツ）
        if (empty($category['target_course']) || $category['target_course'] === 'all') {
          $filtered_categories[] = $category;
        } elseif ($course_filter === 'advance') {
          // アドバンス会員は全コンテンツ表示
          $filtered_categories[] = $category;
        } elseif ($category['target_course'] === $course_filter) {
          // ベーシック会員はベーシックコンテンツのみ表示
          $filtered_categories[] = $category;
        }
      }
      
      return $filtered_categories;
    }

  }