<?php
  require_once dirname(__FILE__) . '/../BaseModel.class.php';
  require_once dirname(__FILE__) . '/../UploadLib.class.php';
  require_once dirname(__FILE__) . '/TagModel.class.php';
  require_once dirname(__FILE__) . '/ContentVideoModel.class.php';

  class ContentModel extends BaseModel {

    // コンテンツの表示、非表示
    const ACTIVE = 1;
    const INACTIVE = 2;

    // FAQコンテンツかどうか
    const IS_FAQ = 1;
    const IS_NOT_FAQ = 2;

    // コース設定
    const TARGET_COURSE_BASIC = 'basic';     // ベーシック（ベーシックコンテンツのみ）
    const TARGET_COURSE_ADVANCE = 'advance'; // アドバンス（全コンテンツ）
    // const TARGET_COURSE_ALL = 'all';         // 全コース（旧来のコンテンツ用）

    public function __construct()
    {
      parent::set_table_name('content_master');
    }


    /**
     * コンテンツ登録
     *
     * @param array $data コンテンツ情報配列
     * @return 成否（boolean）
     */
    public function registerContent($data) {

      // TODO orderのintチェック、桁数チェック
      // TODO order重複チェック

      if(empty($data['content_id'])) {
        // insert時
        // insertしたレコードのIDを取得
        parent::insert($data);
        $content_id = $this->lastInsertId();
        
        // サムネイル画像のアップロード
        $fname_thumbnail = 'cont_' . $content_id . '-' .'thumbnail';
        if(($fname = UploadLib::getInstance()->_upload('thumbnail', 'content', $fname_thumbnail)) !== false) {
          $update_data['thumbnail_url'] = 'contents/content/' . $fname;
        }

        // 資料のアップロード
        $fname_work = 'cont_' . $content_id . '-' .'work';
        if(($fnames = UploadLib::getInstance()->_upload('txt_url', 'works', $fname_work)) !== false) {
          $update_data['text_dl_url'] = 'contents/works/' . $fnames;
        }

        // 書き起こし資料のアップロード
        $fname_document = 'cont_' . $content_id . '-' .'document';
        if(($fnames = UploadLib::getInstance()->_upload('document', 'document', $fname_document)) !== false) {
          $update_data['message_dl_url'] = 'contents/document/' . $fnames;
        }

        // アップロードされたファイルがある場合は更新
        if(!empty($update_data)) {
          parent::update($update_data, array('content_id' => $content_id));
        }
        
        return $content_id;
      } else {
        // update時
        $content_id = $data['content_id'];
        
        // サムネイル画像のアップロード
        $fname_thumbnail = 'cont_' . $content_id . '-' .'thumbnail';
        if(($fname = UploadLib::getInstance()->_upload('thumbnail', 'content', $fname_thumbnail)) !== false) {
          $data['thumbnail_url'] = 'contents/content/' . $fname;
        }

        // 資料のアップロード
        $fname_work = 'cont_' . $content_id . '-' .'work';
        if(($fnames = UploadLib::getInstance()->_upload('txt_url', 'works', $fname_work)) !== false) {
          $data['text_dl_url'] = 'contents/works/' . $fnames;
        }

        // 書き起こし資料のアップロード
        $fname_document = 'cont_' . $content_id . '-' .'document';
        if(($fnames = UploadLib::getInstance()->_upload('document', 'document', $fname_document)) !== false) {
          $data['message_dl_url'] = 'contents/document/' . $fnames;
        }

        parent::update($data, array('content_id' => $content_id));
        return $content_id;
      }
    }


    /**
     * 非公開のコンテンツ一覧取得（バッチ処理用）
     *
     * @return array コンテンツリスト
     */
    public function getInActiveContentList() {
      $content_list = $this->select(array('indicate_flag'=>2));
      return $content_list;
    }

    /**
     * コンテンツにタグを関連付け
     *
     * @param int $content_id コンテンツID
     * @param array $tag_ids タグID配列
     * @return bool 成功時true、失敗時false
     */
    public function setContentTags($content_id, $tag_ids) {
      $pdo = PdoInterface::getInstance();
      
      try {
        $pdo->beginTransaction();
        
        // 既存の関連を削除
        $sql = "DELETE FROM content_tag_relation WHERE content_id = ?";
        $pdo->query($sql, array($content_id));
        
        // 新しい関連を追加
        if (!empty($tag_ids)) {
          $sql = "INSERT INTO content_tag_relation (content_id, tag_id, created_date) VALUES (?, ?, NOW())";
          foreach ($tag_ids as $tag_id) {
            $pdo->query($sql, array($content_id, $tag_id));
          }
        }
        
        $pdo->commit();
        return true;
        
      } catch (Exception $e) {
        $pdo->rollback();
        return false;
      }
    }

    /**
     * コンテンツのタグ一覧を取得
     *
     * @param int $content_id コンテンツID
     * @return array タグ一覧
     */
    public function getContentTags($content_id) {
      $pdo = PdoInterface::getInstance();
      
      $sql = "SELECT 
                  t.tag_id,
                  t.tag_name,
                  t.tag_description
              FROM content_tag_relation ctr
              LEFT JOIN tag_master t ON ctr.tag_id = t.tag_id
              WHERE ctr.content_id = ?
              ORDER BY t.tag_name ASC";
      
      $pdo->query($sql, array($content_id));
      
      $result = array();
      while($rs = $pdo->fetch_assoc()) {
        $result[] = $rs;
      }
      return $result;
    }

    /**
     * コンテンツの動画一覧を取得
     *
     * @param int $content_id コンテンツID
     * @return array 動画一覧
     */
    public function getContentVideos($content_id) {
      $video_model = new ContentVideoModel();
      return $video_model->getVideosByContentId($content_id);
    }

    /**
     * コンテンツに動画を追加
     *
     * @param int $content_id コンテンツID
     * @param array $video_data 動画データ
     * @return bool 成功時true、失敗時false
     */
    public function addContentVideo($content_id, $video_data) {
      $video_data['content_id'] = $content_id;
      $video_model = new ContentVideoModel();
      return $video_model->registerVideo($video_data);
    }

    /**
     * コンテンツの動画を削除
     *
     * @param int $video_id 動画ID
     * @return bool 成功時true、失敗時false
     */
    public function deleteContentVideo($video_id) {
      $video_model = new ContentVideoModel();
      return $video_model->deleteVideo($video_id);
    }

    /**
     * コース別でコンテンツ一覧を取得
     *
     * @param string $course コース（standard, basic, advance, all）
     * @param array $where_data 追加検索条件（オプション）
     * @param array $order_data ソート条件（オプション）
     * @param array $limit_data リミット条件（オプション）
     * @return array コンテンツ一覧
     */
    public function getContentListByCourse($course, $where_data = null, $order_data = null, $limit_data = null) {
      $where_conditions = array();
      
      // コース条件の追加
      if ($course !== null) {
        $where_conditions['target_course'] = $course;
      }
      
      // 追加条件のマージ
      if ($where_data !== null) {
        $where_conditions = array_merge($where_conditions, $where_data);
      }
      
      return parent::select($where_conditions, $order_data, $limit_data);
    }

    /**
     * タグでコンテンツを検索
     *
     * @param array $tag_ids タグID配列
     * @param array $where_data 追加検索条件（オプション）
     * @param array $order_data ソート条件（オプション）
     * @param array $limit_data リミット条件（オプション）
     * @return array コンテンツ一覧
     */
    public function getContentListByTags($tag_ids, $where_data = null, $order_data = null, $limit_data = null) {
      if (empty($tag_ids)) {
        return array();
      }
      
      $pdo = PdoInterface::getInstance();
      
      // 基本クエリ
      $sql = "SELECT DISTINCT cm.* FROM content_master cm
              INNER JOIN content_tag_relation ctr ON cm.content_id = ctr.content_id
              WHERE ctr.tag_id IN (" . implode(',', array_fill(0, count($tag_ids), '?')) . ")";
      
      $params = $tag_ids;
      
      // 追加条件の処理
      if ($where_data !== null) {
        foreach ($where_data as $field => $value) {
          $sql .= " AND cm.{$field} = ?";
          $params[] = $value;
        }
      }
      
      // ソート条件の処理
      if ($order_data !== null) {
        $order_clause = '';
        foreach ($order_data as $field => $direction) {
          if ($direction === BaseModel::ORDER_ASC) {
            $order_clause .= ", cm.{$field} ASC";
          } elseif ($direction === BaseModel::ORDER_DESC) {
            $order_clause .= ", cm.{$field} DESC";
          }
        }
        if ($order_clause) {
          $sql .= " ORDER BY " . trim($order_clause, ",");
        }
      }
      
      // リミット条件の処理
      if ($limit_data !== null) {
        if (isset($limit_data['limit'])) {
          $sql .= " LIMIT " . $limit_data['limit'];
          if (isset($limit_data['offset'])) {
            $sql .= " OFFSET " . $limit_data['offset'];
          }
        }
      }
      
      $pdo->query($sql, $params);
      
      $result = array();
      while($rs = $pdo->fetch_assoc()) {
        $result[] = $rs;
      }
      return $result;
    }

    /**
     * コンテンツ一覧をタグ情報と結合して取得
     *
     * @param array $where_data 検索条件（オプション）
     * @param array $order_data ソート条件（オプション）
     * @param array $limit_data リミット条件（オプション）
     * @return array コンテンツ一覧（タグ情報含む）
     */
    public function getContentListWithTags($where_data = null, $order_data = null, $limit_data = null) {
      $pdo = PdoInterface::getInstance();
      
      $sql = "SELECT 
                  cm.*,
                  GROUP_CONCAT(t.tag_name ORDER BY t.tag_name SEPARATOR ',') as tag_names,
                  GROUP_CONCAT(t.tag_id ORDER BY t.tag_name SEPARATOR ',') as tag_ids
              FROM content_master cm
              LEFT JOIN content_tag_relation ctr ON cm.content_id = ctr.content_id
              LEFT JOIN tag_master t ON ctr.tag_id = t.tag_id";
      
      $where_clause = '';
      $params = array();
      
      // 検索条件の処理
      if ($where_data !== null) {
        $where_conditions = array();
        foreach ($where_data as $field => $value) {
          $where_conditions[] = "cm.{$field} = ?";
          $params[] = $value;
        }
        if ($where_conditions) {
          $where_clause = " WHERE " . implode(' AND ', $where_conditions);
        }
      }
      
      $sql .= $where_clause . " GROUP BY cm.content_id";
      
      // ソート条件の処理
      if ($order_data !== null) {
        $order_clause = '';
        foreach ($order_data as $field => $direction) {
          if ($direction === BaseModel::ORDER_ASC) {
            $order_clause .= ", cm.{$field} ASC";
          } elseif ($direction === BaseModel::ORDER_DESC) {
            $order_clause .= ", cm.{$field} DESC";
          }
        }
        if ($order_clause) {
          $sql .= " ORDER BY " . trim($order_clause, ",");
        }
      }
      
      // リミット条件の処理
      if ($limit_data !== null) {
        if (isset($limit_data['limit'])) {
          $sql .= " LIMIT " . $limit_data['limit'];
          if (isset($limit_data['offset'])) {
            $sql .= " OFFSET " . $limit_data['offset'];
          }
        }
      }
      
      $pdo->query($sql, $params);
      
      $result = array();
      while($rs = $pdo->fetch_assoc()) {
        $result[] = $rs;
      }
      return $result;
    }

    /**
     * コンテンツの動画数を取得
     *
     * @param int $content_id コンテンツID
     * @return int 動画数
     */
    public function getVideoCount($content_id) {
      $video_model = new ContentVideoModel();
      return $video_model->getVideoCountByContentId($content_id);
    }

    /**
     * 有効なコース一覧を取得
     *
     * @return array コース一覧
     */
    public function getAvailableCourses() {
        return array(
        self::TARGET_COURSE_BASIC => 'ベーシック',
        self::TARGET_COURSE_ADVANCE => 'アドバンス',
        // self::TARGET_COURSE_ALL => '全コース'
        );
    }

    /**
     * コース名を取得
     *
     * @param string $course コースコード
     * @return string コース名
     */
    public function getCourseName($course) {
      $courses = $this->getAvailableCourses();
      return isset($courses[$course]) ? $courses[$course] : '不明';
    }


  }