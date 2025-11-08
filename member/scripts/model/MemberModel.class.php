<?php
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
      $pdo = PdoInterface::getInstance();

      $total_sql = "SELECT COUNT(*) AS total
                    FROM content_master cm
                    WHERE cm.indicate_flag = ? AND cm.category_id = ?";
      $total_params = array(ContentModel::ACTIVE, $category_id);

      if ($course_filter === ContentModel::TARGET_COURSE_BASIC) {
        $total_sql .= " AND (cm.target_course = ? OR cm.target_course IS NULL OR cm.target_course = '' OR cm.target_course = 'all')";
        $total_params[] = $course_filter;
      }

      $pdo->query($total_sql, $total_params);
      $total_row = $pdo->fetch_assoc();
      $total = isset($total_row['total']) ? (int)$total_row['total'] : 0;

      if ($total === 0) {
        return array('total' => 0, 'completed' => 0);
      }

      $completed_sql = "SELECT COUNT(*) AS completed
                        FROM member_content_relation mcr
                        INNER JOIN content_master cm ON cm.content_id = mcr.content_id
                        WHERE mcr.member_id = ? AND cm.indicate_flag = ? AND cm.category_id = ?";
      $completed_params = array($member_id, ContentModel::ACTIVE, $category_id);

      if ($course_filter === ContentModel::TARGET_COURSE_BASIC) {
        $completed_sql .= " AND (cm.target_course = ? OR cm.target_course IS NULL OR cm.target_course = '' OR cm.target_course = 'all')";
        $completed_params[] = $course_filter;
      }

      $pdo->query($completed_sql, $completed_params);
      $completed_row = $pdo->fetch_assoc();
      $completed = isset($completed_row['completed']) ? (int)$completed_row['completed'] : 0;

      return array('total' => $total, 'completed' => $completed);
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
      
      $pdo = PdoInterface::getInstance();
      
      // 基本クエリ
      $sql = "SELECT 
                  cm.content_id,
                  cm.content_title,
                  cm.target_course,
                  CASE 
                    WHEN mcr.member_id IS NOT NULL THEN 1 
                    ELSE 0 
                  END as is_completed,
                  CASE 
                    WHEN mcr.member_id IS NOT NULL THEN mcr.created_date 
                    ELSE NULL 
                  END as completed_date
              FROM content_master cm
              LEFT JOIN member_content_relation mcr ON cm.content_id = mcr.content_id AND mcr.member_id = ?
              WHERE cm.indicate_flag = ?";
      
      $params = array($member_id, ContentModel::ACTIVE);
      
      // コースフィルタの適用
      if ($course_filter === ContentModel::TARGET_COURSE_BASIC) {
        $sql .= " AND (cm.target_course = ? OR cm.target_course IS NULL OR cm.target_course = '' OR cm.target_course = 'all')";
        $params[] = $course_filter;
      } elseif ($course_filter !== null && $course_filter !== ContentModel::TARGET_COURSE_ADVANCE) {
        $sql .= " AND cm.target_course = ?";
        $params[] = $course_filter;
      }
      
      // カテゴリーフィルタの適用
      if ($category_id !== null) {
        $sql .= " AND cm.category_id = ?";
        $params[] = $category_id;
      }
      
      $sql .= " ORDER BY cm.content_id ASC";
      
      $pdo->query($sql, $params);
      
      $result = array();
      while($rs = $pdo->fetch_assoc()) {
        $result[] = $rs;
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

      $member_data = $member[0];
      $course_filter = $this->getCourseFilter($member_data['select_course']);

      $placeholders = implode(',', array_fill(0, count($category_ids), '?'));
      $pdo = PdoInterface::getInstance();

      $totals_sql = "SELECT cm.category_id, COUNT(*) AS total
                     FROM content_master cm
                     WHERE cm.indicate_flag = ?
                       AND cm.category_id IN ($placeholders)";
      $totals_params = array_merge(array(ContentModel::ACTIVE), $category_ids);

      if ($course_filter === ContentModel::TARGET_COURSE_BASIC) {
        $totals_sql .= " AND (cm.target_course = ? OR cm.target_course IS NULL OR cm.target_course = '' OR cm.target_course = 'all')";
        $totals_params[] = $course_filter;
      } elseif ($course_filter !== null && $course_filter !== ContentModel::TARGET_COURSE_ADVANCE) {
        $totals_sql .= " AND cm.target_course = ?";
        $totals_params[] = $course_filter;
      }

      $totals_sql .= " GROUP BY cm.category_id";

      $pdo->query($totals_sql, $totals_params);
      $totals = array();
      while($row = $pdo->fetch_assoc()) {
        $category_id = (int)$row['category_id'];
        $totals[$category_id] = (int)$row['total'];
      }

      $completes_sql = "SELECT cm.category_id, COUNT(*) AS completed
                        FROM member_content_relation mcr
                        INNER JOIN content_master cm ON cm.content_id = mcr.content_id
                        WHERE mcr.member_id = ?
                          AND cm.indicate_flag = ?
                          AND cm.category_id IN ($placeholders)";
      $completes_params = array_merge(array($member_id, ContentModel::ACTIVE), $category_ids);

      if ($course_filter === ContentModel::TARGET_COURSE_BASIC) {
        $completes_sql .= " AND (cm.target_course = ? OR cm.target_course IS NULL OR cm.target_course = '' OR cm.target_course = 'all')";
        $completes_params[] = $course_filter;
      } elseif ($course_filter !== null && $course_filter !== ContentModel::TARGET_COURSE_ADVANCE) {
        $completes_sql .= " AND cm.target_course = ?";
        $completes_params[] = $course_filter;
      }

      $completes_sql .= " GROUP BY cm.category_id";

      $pdo->query($completes_sql, $completes_params);
      $completes = array();
      while($row = $pdo->fetch_assoc()) {
        $category_id = (int)$row['category_id'];
        $completes[$category_id] = (int)$row['completed'];
      }

      $results = array();
      foreach ($category_ids as $category_id) {
        $total = isset($totals[$category_id]) ? $totals[$category_id] : 0;
        $completed = isset($completes[$category_id]) ? $completes[$category_id] : 0;
        $completion_rate = $total > 0 ? (int)round($completed / $total * 100) : 0;
        $results[$category_id] = array(
          'total_contents' => $total,
          'completed_contents' => $completed,
          'completion_rate' => $completion_rate
        );
      }

      return $results;
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
      
      if ($course_filter !== null) {
        $where_conditions['target_course'] = $course_filter;
      }
      
      return $content_model->count($where_conditions);
    }


  }