<?php
  require_once dirname(__FILE__) . '/../BaseModel.class.php';
  require_once dirname(__FILE__) . '/../UploadLib.class.php';
  require_once dirname(__FILE__) . '/ContentModel.class.php';

  class CategoryModel extends BaseModel {

    /**
     * 直近のエラーメッセージ
     *
     * @var string|null
     */
    private $last_error_message = null;

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

      // 直近のエラーを初期化
      $this->last_error_message = null;

      // TODO ナンバーのintチェック、桁数チェック

      if(isset($data['category_number'])) {
        $data['category_number'] = is_numeric($data['category_number'])
          ? (int)$data['category_number']
          : $data['category_number'];
      }

      // ナンバー重複チェック
      if(isset($data['category_number'])) {
        $existing = $this->select(array('category_number' => $data['category_number']));
        if(!empty($existing)) {
          $current_id = isset($data['category_id']) ? (int)$data['category_id'] : 0;
          $conflict = null;
          foreach ($existing as $item) {
            if(!empty($item['category_id']) && (int)$item['category_id'] !== $current_id) {
              $conflict = $item;
              break;
            }
          }
          if($conflict !== null) {
            $this->last_error_message = '選択したナンバーは既に使用されています。別のナンバーを選択してください。';
            return false;
          }
        }
      }

      if(!isset($data['use_week_flag'])) {
        $data['use_week_flag'] = 1;
      } else {
        $data['use_week_flag'] = (int)$data['use_week_flag'];
      }

      if(empty($data['category_id'])) {
        // insert時
        // insertしたレコードのIDを取得
        try {
          parent::insert($data);
          $category_id = $this->lastInsertId();
        } catch (\PDOException $e) {
          $this->last_error_message = 'カテゴリーの登録に失敗しました。時間をおいて再度お試しください。';
          if (strpos($e->getMessage(), 'category_number') !== false) {
            $this->last_error_message = '選択したナンバーは既に使用されています。別のナンバーを選択してください。';
          }
          return false;
        }
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

      try {
        $result = parent::update($data, array('category_id' => $category_id));
      } catch (\PDOException $e) {
        $this->last_error_message = 'カテゴリーの更新に失敗しました。時間をおいて再度お試しください。';
        if (strpos($e->getMessage(), 'category_number') !== false) {
          $this->last_error_message = '選択したナンバーは既に使用されています。別のナンバーを選択してください。';
        }
        return false;
      }

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
     * 直近のエラーメッセージを取得
     *
     * @return string|null
     */
    public function getLastErrorMessage() {
      return $this->last_error_message;
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