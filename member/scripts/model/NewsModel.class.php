<?php
  require_once dirname(__FILE__) . '/../env.php';
  require_once dirname(__FILE__) . '/../BaseModel.class.php';
  require_once dirname(__FILE__) . '/../model/ContentModel.class.php';


  class NewsModel extends BaseModel {

    // コンテンツの表示、非表示
    const ACTIVE = 1;
    const INACTIVE = 2;

    // 対象コース
    const TARGET_COURSE_ALL = 'all';

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
     * @param string|null $course_filter 会員のコースフィルター
     * @return array ニュースリスト
     */
    public function getNewsList($num = NULL, $course_filter = null) {
      $news_list = $this->select(
        array('is_active'=>1),
        array('note_date'=>self::ORDER_DESC)
      );

      $now = new DateTimeImmutable();
      $news_list = array_values(array_filter($news_list, function($news) use ($now) {
        $publishable = isset($news['note_date']) ? $news['note_date'] : null;
        return isPublishableNow($publishable, $now);
      }));

      if($course_filter === null) {
        if(!empty($num)) {
          return array_slice($news_list, 0, (int)$num);
        }
        return $news_list;
      }

      $filtered = array();
      $limit = !empty($num) ? (int)$num : null;
      foreach ($news_list as $news) {
        if ($this->isVisibleForCourse($news, $course_filter)) {
          $filtered[] = $news;
          if ($limit !== null && count($filtered) >= $limit) {
            break;
          }
        }
      }

      return $filtered;
    }


    /**
     * 非公開のニュース取得（バッチ処理用）
     * @return array ニュースリスト
     */
    public function getInActiveNewsList() {
      $news_list = $this->select(array('is_active'=>0), array('note_date'=>self::ORDER_DESC));
      return $news_list;
    }


    /**
     * ニュースが指定コースの会員に表示可能かを判定
     *
     * @param array $news ニュースレコード
     * @param string $course_filter 会員のコースフィルター
     * @return bool
     */
    public function isVisibleForCourse($news, $course_filter) {
      if ($course_filter === null) {
        return true;
      }

      $normalized_filter = $this->normalizeCourseFilter($course_filter);
      $target_course = isset($news['target_course']) ? $news['target_course'] : null;
      $normalized_target = $this->normalizeTargetCourse($target_course);

      if ($normalized_target === self::TARGET_COURSE_ALL) {
        return true;
      }

      return $normalized_target === $normalized_filter;
    }


    /**
     * 会員コースフィルターを正規化
     *
     * @param string $course_filter
     * @return string
     */
    private function normalizeCourseFilter($course_filter) {
      $value = strtolower(trim((string)$course_filter));
      if ($value === ContentModel::TARGET_COURSE_BASIC) {
        return ContentModel::TARGET_COURSE_BASIC;
      }
      return ContentModel::TARGET_COURSE_ADVANCE;
    }


    /**
     * ニュース対象コース値を正規化
     *
     * @param string|null $target_course
     * @return string
     */
    private function normalizeTargetCourse($target_course) {
      if ($target_course === null) {
        return self::TARGET_COURSE_ALL;
      }

      $value = strtolower(trim((string)$target_course));
      if ($value === '' || $value === self::TARGET_COURSE_ALL) {
        return self::TARGET_COURSE_ALL;
      }
      if ($value === ContentModel::TARGET_COURSE_BASIC) {
        return ContentModel::TARGET_COURSE_BASIC;
      }
      if ($value === ContentModel::TARGET_COURSE_ADVANCE) {
        return ContentModel::TARGET_COURSE_ADVANCE;
      }
      return self::TARGET_COURSE_ALL;
    }

  }