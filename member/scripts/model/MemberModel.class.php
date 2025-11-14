<?php
  require_once dirname(__FILE__) . '/../env.php';
  require_once dirname(__FILE__) . '/../BaseModel.class.php';
  require_once dirname(__FILE__) . '/../PdoInterface.class.php';
  require_once dirname(__FILE__) . '/../model/CategoryModel.class.php';
  require_once dirname(__FILE__) . '/../model/ContentModel.class.php';
  require_once dirname(__FILE__) . '/../model/UploadModel.class.php';
  require_once dirname(__FILE__) . '/../model/MemberContentRelation.class.php';


  class MemberModel extends BaseModel {

    // コース設定（既存のselect_courseフィールドの値）
    const COURSE_PREMIUM = 1;    // プレミアム
    const COURSE_BASIC = 2;      // ベーシック
    const COURSE_OTHER = 3;      // その他

    // ログイン結果ステータス
    const LOGIN_STATUS_SUCCESS = 'success';
    const LOGIN_STATUS_INVALID_CREDENTIALS = 'invalid_credentials';
    const LOGIN_STATUS_EXPIRED = 'expired';
    const LOGIN_STATUS_ERROR = 'error';

    public function __construct($member_id = NULL) {
      parent::set_table_name('member_master');
    }

    /**
     * カテゴリーごとの総コンテンツ数と完了数を取得
     *
     * @param int $member_id 会員ID
     * @param int $category_id カテゴリーID
     * @return array{total:int, completed:int}
     */
    private function getCategoryContentStats($member_id, $category_id) {
      $member = parent::select(array('member_id' => $member_id));
      if (empty($member)) {
        return array('total' => 0, 'completed' => 0);
      }

      $member_data = $member[0];
      $course_filter = $this->getCourseFilter($member_data['select_course']);

      $content_model = new ContentModel();
      $contents = $content_model->select(
        array(
          'category_id' => $category_id,
          'indicate_flag' => ContentModel::ACTIVE,
        ),
        array(
          'display_order' => ContentModel::ORDER_ASC,
          'content_id' => ContentModel::ORDER_ASC,
        )
      );

      $available_contents = array();
      foreach ($contents as $content) {
        $pub_date = isset($content['pub_date']) ? $content['pub_date'] : null;
        if (!isPublishableNow($pub_date)) {
          continue;
        }
        if (!$this->isContentVisibleForCourse($content, $course_filter)) {
          continue;
        }
        $content_id = isset($content['content_id']) ? (int)$content['content_id'] : 0;
        if ($content_id === 0) {
          continue;
        }
        $available_contents[$content_id] = true;
      }

      $total = count($available_contents);
      if ($total === 0) {
        return array('total' => 0, 'completed' => 0);
      }

      $relation_model = new MemberContentRelation();
      $completed_records = $relation_model->select(array(
        'member_id' => $member_id,
        'category_id' => $category_id,
      ));

      $completed = 0;
      foreach ($completed_records as $record) {
        $content_id = isset($record['content_id']) ? (int)$record['content_id'] : 0;
        if ($content_id !== 0 && isset($available_contents[$content_id])) {
          $completed++;
        }
      }

      return array('total' => $total, 'completed' => $completed);
    }

    /**
     * 会員コースに対してコンテンツが表示可能かを判定
     *
     * @param array $content コンテンツ情報
     * @param string|null $course_filter 会員のコースフィルタ
     * @return bool
     */
    private function isContentVisibleForCourse(array $content, $course_filter) {
      if ($course_filter === null || $course_filter === ContentModel::TARGET_COURSE_ADVANCE) {
        return true;
      }

      $target_course = isset($content['target_course']) ? strtolower((string)$content['target_course']) : '';
      if ($target_course === '' || $target_course === 'all' || $target_course === ContentModel::TARGET_COURSE_ADVANCE) {
        return true;
      }

      return $target_course === $course_filter;
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
     * ログイン処理
     *
     * @param string $login_mail メールアドレス
     * @param string $password パスワード
     * @return array{status:string, member?:array}
     */
    public function login($input_mail, $input_pass) {
      $member = $this->getMemberByMail($input_mail);
      if (empty($member)) {
        return array(
          'status' => self::LOGIN_STATUS_INVALID_CREDENTIALS
        );
      }

      $pass = $member["login_password"];
      $now = time();//現在の時間
      $stop_date_raw = isset($member["stop_date"]) ? $member["stop_date"] : null;//退会時間
      $stop_timestamp = $stop_date_raw ? strtotime($stop_date_raw) : false;
      if($stop_timestamp && $stop_timestamp < $now) {
        return array(
          'status' => self::LOGIN_STATUS_EXPIRED,
          'member' => $member
        );
      }

      if($input_pass == $pass) {
        return array(
          'status' => self::LOGIN_STATUS_SUCCESS,
          'member' => $member
        );
      }

      return array(
        'status' => self::LOGIN_STATUS_INVALID_CREDENTIALS,
        'member' => $member
      );
    }


    /**
     * 課題（カテゴリー）の達成率を計算し返す。
     *
     * @param int $member_id 会員ID
     * @param int $category_id カテゴリーID
     * @return 達成率：整数
     */
    public function getScore($member_id, $category_id) {
      $stats = $this->getCategoryContentStats($member_id, $category_id);
      if ($stats['total'] === 0) {
        return 0;
      }
      return (int)round($stats['completed'] / $stats['total'] * 100);
    }

    /**
     * 会員のコース別コンテンツ一覧を取得
     *
     * @param int $member_id 会員ID
     * @param array $where_data 追加検索条件（オプション）
     * @param array $order_data ソート条件（オプション）
     * @param array $limit_data リミット条件（オプション）
     * @return array コンテンツ一覧
     */
    public function getContentListByMemberCourse($member_id, $where_data = null, $order_data = null, $limit_data = null) {
      // 会員のコース情報を取得
      $member = parent::select(array('member_id' => $member_id));
      if (empty($member)) {
        return array();
      }
      
      $member_course = $member[0]['select_course'];
      $content_model = new ContentModel();
      
      // コースに応じたコンテンツフィルタリング
      $course_filter = $this->getCourseFilter($member_course);
      
      // 検索条件のマージ
      $search_conditions = array();
      if ($course_filter !== null) {
        $search_conditions['target_course'] = $course_filter;
      }
      if ($where_data !== null) {
        $search_conditions = array_merge($search_conditions, $where_data);
      }
      
      return $content_model->getContentListByCourse($course_filter, $where_data, $order_data, $limit_data);
    }

    /**
     * 会員のコースに応じたコンテンツフィルタを取得
     *
     * @param int $member_course 会員のコース（select_courseフィールドの値）
     * @return string|null フィルタ条件
     */
    public function getCourseFilter($member_course) {
      switch ($member_course) {
        case self::COURSE_PREMIUM:  // 1: プレミアム → アドバンス
        case self::COURSE_OTHER:    // 3: その他 → アドバンス
          return ContentModel::TARGET_COURSE_ADVANCE;  // アドバンス（全コンテンツ表示）
        case self::COURSE_BASIC:    // 2: ベーシック
          return ContentModel::TARGET_COURSE_BASIC;    // ベーシック（ベーシックコンテンツのみ）
        default:
          return ContentModel::TARGET_COURSE_ADVANCE;      // 不明 → アドバンス
      }
    }

    /**
     * 会員のコース名を取得
     *
     * @param int $member_course 会員のコース（select_courseフィールドの値）
     * @return string コース名
     */
    public function getMemberCourseName($member_course) {
      switch ($member_course) {
        case self::COURSE_PREMIUM:
          return 'プレミアム';
        case self::COURSE_BASIC:
          return 'ベーシック';
        case self::COURSE_OTHER:
          return 'その他';
        default:
          return '不明';
      }
    }

    /**
     * 会員のコース一覧を取得
     *
     * @return array コース一覧
     */
    public function getAvailableMemberCourses() {
      return array(
        self::COURSE_PREMIUM => 'プレミアム',
        self::COURSE_BASIC => 'ベーシック',
        self::COURSE_OTHER => 'その他'
      );
    }

    /**
     * 会員のコース別進捗情報を取得
     *
     * @param int $member_id 会員ID
     * @param int $category_id カテゴリーID（オプション）
     * @return array 進捗情報
     */
    public function getMemberProgressByCourse($member_id, $category_id = null) {
      $member = parent::select(array('member_id' => $member_id));
      if (empty($member)) {
        return array();
      }

      $member_data = $member[0];
      $course_filter = $this->getCourseFilter($member_data['select_course']);

      $content_model = new ContentModel();
      $conditions = array('indicate_flag' => ContentModel::ACTIVE);
      if ($category_id !== null) {
        $conditions['category_id'] = $category_id;
      }

      $contents = $content_model->select(
        $conditions,
        array(
          'display_order' => ContentModel::ORDER_ASC,
          'content_id' => ContentModel::ORDER_ASC,
        )
      );

      $filtered_contents = array();
      foreach ($contents as $content) {
        $pub_date = isset($content['pub_date']) ? $content['pub_date'] : null;
        if (!isPublishableNow($pub_date)) {
          continue;
        }
        if (!$this->isContentVisibleForCourse($content, $course_filter)) {
          continue;
        }
        $content_id = isset($content['content_id']) ? (int)$content['content_id'] : 0;
        if ($content_id === 0) {
          continue;
        }
        $filtered_contents[$content_id] = $content;
      }

      if (empty($filtered_contents)) {
        return array();
      }

      $relation_model = new MemberContentRelation();
      $relation_conditions = array('member_id' => $member_id);
      if ($category_id !== null) {
        $relation_conditions['category_id'] = $category_id;
      }
      $completed_records = $relation_model->select($relation_conditions);

      $completed_map = array();
      foreach ($completed_records as $record) {
        $content_id = isset($record['content_id']) ? (int)$record['content_id'] : 0;
        if ($content_id !== 0) {
          $completed_map[$content_id] = $record;
        }
      }

      $result = array();
      foreach ($filtered_contents as $content_id => $content) {
        $is_completed = isset($completed_map[$content_id]);
        $completed_date = $is_completed && isset($completed_map[$content_id]['created_date'])
          ? $completed_map[$content_id]['created_date']
          : null;
        $result[] = array(
          'content_id' => $content_id,
          'content_title' => isset($content['content_title']) ? $content['content_title'] : '',
          'target_course' => isset($content['target_course']) ? $content['target_course'] : null,
          'is_completed' => $is_completed ? 1 : 0,
          'completed_date' => $completed_date,
        );
      }

      return $result;
    }

    /**
     * 会員のコース別達成率を取得
     *
     * @param int $member_id 会員ID
     * @param int $category_id カテゴリーID（オプション）
     * @return array 達成率情報
     */
    public function getMemberCourseProgress($member_id, $category_id = null) {
      $progress_data = $this->getMemberProgressByCourse($member_id, $category_id);
      
      if ($category_id !== null) {
        $stats = $this->getCategoryContentStats($member_id, $category_id);
        $total_contents = $stats['total'];
        $completed_contents = $stats['completed'];
      } else {
        $total_contents = count($progress_data);
        $completed_contents = 0;
      
        foreach ($progress_data as $content) {
          if ($content['is_completed']) {
            $completed_contents++;
          }
        }
      }
      
      $completion_rate = $total_contents > 0 ? round($completed_contents / $total_contents * 100) : 0;
      
      return array(
        'total_contents' => $total_contents,
        'completed_contents' => $completed_contents,
        'completion_rate' => $completion_rate,
        'progress_data' => $progress_data
      );
    }

    /**
     * カテゴリーID一覧に対して進捗情報をまとめて取得
     *
     * @param int $member_id 会員ID
     * @param array $category_ids カテゴリーID配列
     * @return array カテゴリーIDをキーにした進捗情報
     */
    public function getMemberCourseProgressByCategories($member_id, array $category_ids) {
      $category_ids = array_values(array_unique(array_filter(array_map('intval', $category_ids))));
      if (empty($category_ids)) {
        return array();
      }

      $member = parent::select(array('member_id' => $member_id));
      if (empty($member)) {
        return array();
      }

      $progress = array();
      foreach ($category_ids as $category_id) {
        $stats = $this->getCategoryContentStats($member_id, $category_id);
        $total = isset($stats['total']) ? (int)$stats['total'] : 0;
        $completed = isset($stats['completed']) ? (int)$stats['completed'] : 0;
        $completion_rate = $total > 0 ? (int)round($completed / $total * 100) : 0;
        $progress[$category_id] = array(
          'total_contents' => $total,
          'completed_contents' => $completed,
          'completion_rate' => $completion_rate,
        );
      }

      return $progress;
    }

    /**
     * 会員のコース別視聴可能コンテンツ数を取得
     *
     * @param int $member_id 会員ID
     * @return int 視聴可能コンテンツ数
     */
    public function getAvailableContentCount($member_id) {
      $member = parent::select(array('member_id' => $member_id));
      if (empty($member)) {
        return 0;
      }
      
      $member_course = $member[0]['select_course'];
      $course_filter = $this->getCourseFilter($member_course);
      
      $content_model = new ContentModel();
      $where_conditions = array('indicate_flag' => ContentModel::ACTIVE);

      $contents = $content_model->select($where_conditions);
      $available_count = 0;
      foreach ($contents as $content) {
        $pub_date = isset($content['pub_date']) ? $content['pub_date'] : null;
        if (!isPublishableNow($pub_date)) {
          continue;
        }
        if (!$this->isContentVisibleForCourse($content, $course_filter)) {
          continue;
        }
        $available_count++;
      }

      return $available_count;
    }


  }