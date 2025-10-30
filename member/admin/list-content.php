<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/CategoryModel.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/ContentModel.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/TagModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('admin') === false) {
    header("Location: login.php");
    exit;
  }

  // 利用可能なコース一覧を取得
  $content_model = new ContentModel();
  $available_courses = $content_model->getAvailableCourses();
  
  // 全タグ一覧を取得
  $tag_model = new TagModel();
  $all_tags = $tag_model->getTagList(null, array('tag_name' => BaseModel::ORDER_ASC));

  // フィルタ条件を取得
  $filter_course = isset($_GET['filter_course']) ? $_GET['filter_course'] : '';
  $filter_tags = isset($_GET['filter_tags']) ? $_GET['filter_tags'] : array();
  if (is_string($filter_tags)) {
    $filter_tags = !empty($filter_tags) ? explode(',', $filter_tags) : array();
  }

  // コンテンツ一覧取得
  $content_list = $content_model->select(null, array('content_id' => BaseModel::ORDER_ASC));
  
  // フィルタを適用
  $filtered_content_list = array();
  foreach ($content_list as $content) {
    // コース別フィルタ
    if (!empty($filter_course)) {
      $target_course = isset($content['target_course']) ? $content['target_course'] : '';
      // フィルタが指定されている場合：
      // - コンテンツが指定コースと一致 → 表示
      // - コンテンツが'all'または空(NULL) → どちらのフィルタでも表示
      if ($target_course !== $filter_course && $target_course !== ContentModel::TARGET_COURSE_ALL && !empty($target_course)) {
        continue;
      }
    }
    
    // タグ別フィルタ
    $content_tags = $content_model->getContentTags($content['content_id']);
    $content['tags'] = $content_tags;
    
    if (!empty($filter_tags)) {
      $content_tag_ids = array_column($content_tags, 'tag_id');
      $has_matching_tag = false;
      foreach ($filter_tags as $tag_id) {
        if (in_array($tag_id, $content_tag_ids)) {
          $has_matching_tag = true;
          break;
        }
      }
      if (!$has_matching_tag) {
        continue;
      }
    }
    
    $filtered_content_list[] = $content;
  }
  
  $content_list = $filtered_content_list;

  // カテゴリ表示名とidの紐付け表を作成
  $category_model = new CategoryModel();
  $category_list = $category_model->select();
  $number_list = array();
  foreach ($category_list as $ctg) {
    $number_list[$ctg['category_id']] = $ctg['category_number'];
  }

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>コンテンツ一覧 | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />
<style>
  .filter-area {
    background-color: #f5f5f5;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
    border: 1px solid #ddd;
  }
  .filter-row {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
    flex-wrap: wrap;
    align-items: flex-start;
  }
  .filter-group {
    flex: 1;
    min-width: 200px;
  }
  .filter-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
    color: #333;
  }
  .filter-group select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 3px;
  }
  .tag-filter-group {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }
  .tag-filter-item {
    display: flex;
    align-items: center;
  }
  .tag-filter-item input[type="checkbox"] {
    margin-right: 5px;
  }
  .filter-buttons {
    display: flex;
    gap: 10px;
  }
  .filter-buttons button {
    padding: 8px 16px;
    background-color: #2196F3;
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-size: 14px;
  }
  .filter-buttons button:hover {
    background-color: #0b7dda;
  }
  .filter-buttons a {
    padding: 8px 16px;
    background-color: #757575;
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
  }
  .filter-buttons a:hover {
    background-color: #616161;
  }
</style>
<script>
</script>
</head>

<body>

<!-- Wrapper starts -->
<div id="wrapper">
  <div class="InBox"><!-- INBOX -->
    <h1>コンテンツ一覧</h1>

<?php
  $menu_active = 'cnts';
  include_once 'menu.php';
?>

    <a href="register-content.php" class="regster">
      <div class="submenu Tab">新規登録</div>
    </a>
    
    <!-- 絞り込みエリア -->
    <div class="filter-area">
      <h3 style="margin-top: 0;">絞り込み</h3>
      <form method="GET" action="list-content.php">
        <div class="filter-row">
          <div class="filter-group">
            <label for="filter_course">対象コース</label>
            <select name="filter_course" id="filter_course">
              <option value="">-- すべて --</option>
              <?php foreach ($available_courses as $course_key => $course_name): ?>
              <option value="<?php echo htmlspecialchars($course_key, ENT_QUOTES, 'UTF-8'); ?>" 
                <?php echo ($filter_course === $course_key) ? 'selected="selected"' : ''; ?>>
                <?php echo htmlspecialchars($course_name, ENT_QUOTES, 'UTF-8'); ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="filter-group" style="flex: 2;">
            <label>タグ</label>
            <div class="tag-filter-group">
              <?php if (!empty($all_tags)): ?>
                <?php foreach ($all_tags as $tag): ?>
                <div class="tag-filter-item">
                  <input type="checkbox" name="filter_tags" value="<?php echo htmlspecialchars($tag['tag_id'], ENT_QUOTES, 'UTF-8'); ?>" 
                    id="tag_<?php echo htmlspecialchars($tag['tag_id'], ENT_QUOTES, 'UTF-8'); ?>"
                    <?php echo (in_array($tag['tag_id'], $filter_tags)) ? 'checked="checked"' : ''; ?>>
                  <label for="tag_<?php echo htmlspecialchars($tag['tag_id'], ENT_QUOTES, 'UTF-8'); ?>" style="margin: 0; font-weight: normal; cursor: pointer;">
                    <?php echo htmlspecialchars($tag['tag_name'], ENT_QUOTES, 'UTF-8'); ?>
                  </label>
                </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p style="margin: 0; color: #999;">タグが登録されていません</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
        
        <div class="filter-buttons">
          <button type="submit">絞り込み</button>
          <a href="list-content.php">クリア</a>
        </div>
      </form>
    </div>
    
    <table class="member">
      <tr>
        <th style="width: 30px;">ID</th>
        <th>カテゴリー</th>
        <th>Week</th>
        <th>タイトル</th>
        <th>対象コース</th>
        <th>タグ</th>
        <th>公開日</th>
        <th style="width: 30px;">詳細</th>
      </tr>
      <?php foreach ($content_list as $content) : ?>
      <tr>
        <td><?php echo $content['content_id']; ?></td>
<?php if ($content['category_id'] == '5'): ?>
        <td>イマジンラジオ</td>
<?php elseif ($content['category_id'] == '6'): ?>
        <td>QAライブ動画</td>
<?php else: ?>
        <td>Lesson<?php echo $number_list[$content['category_id']]; ?></td>
<?php endif; ?>
        <td><?php echo $content['content_week']; ?></td>
        <td><?php echo htmlspecialchars($content['content_title'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php echo htmlspecialchars($content_model->getCourseName($content['target_course']), ENT_QUOTES, 'UTF-8'); ?></td>
        <td>
          <?php if(!empty($content['tags'])): ?>
            <?php foreach($content['tags'] as $tag): ?>
              <span style="background-color: #e3f2fd; padding: 2px 6px; margin: 1px; border-radius: 3px; font-size: 0.8em;"><?php echo htmlspecialchars($tag['tag_name'], ENT_QUOTES, 'UTF-8'); ?></span>
            <?php endforeach; ?>
          <?php else: ?>
            <span style="color: #999;">タグなし</span>
          <?php endif; ?>
        </td>
        <td><?php echo htmlspecialchars($content["pub_date"], ENT_QUOTES, 'UTF-8'); ?></td>
        <td style="text-align:center"><input type="button" value="詳細" onclick="location.href='edit-content.php?cont_id=<?php echo $content['content_id']; ?>'"></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->

</body>
</html>