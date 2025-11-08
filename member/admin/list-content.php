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

  $toast_message = '';
  if (isset($_GET['status'])) {
    switch ($_GET['status']) {
      case 'created':
        $toast_message = 'コンテンツを登録しました。';
        break;
      case 'updated':
        $toast_message = 'コンテンツを更新しました。';
        break;
      case 'deleted':
        $toast_message = 'コンテンツを削除しました。';
        break;
      case 'toggled':
        $toast_message = '表示状態を更新しました。';
        break;
    }
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
      // - コンテンツが空(NULL) → どちらのフィルタでも表示
      if ($target_course !== $filter_course && $target_course !== ContentModel::TARGET_COURSE_ADVANCE && !empty($target_course)) {
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
    flex-direction: column;
    gap: 10px;
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
  .status-toggle-form {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
  }
  .status-toggle-label {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 22px;
    margin-right: 8px;
  }
  .status-toggle-label input {
    opacity: 0;
    width: 0;
    height: 0;
  }
  .status-toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .3s;
    border-radius: 22px;
  }
  .status-toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 2px;
    bottom: 2px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
  }
  .status-toggle-input:checked + .status-toggle-slider {
    background-color: #4CAF50;
  }
  .status-toggle-input:checked + .status-toggle-slider:before {
    transform: translateX(22px);
  }
  .status-toggle-text {
    font-size: 12px;
    font-weight: bold;
    color: #555;
  }
  .status-toggle-text.active {
    color: #2e7d32;
  }
  .status-toggle-text.inactive {
    color: #757575;
  }
  .status-toggle-header,
  .status-toggle-cell {
    text-align: center;
    width: 80px;
  }
  .btn-detail {
    display: inline-block;
    padding: 6px 12px;
    background-color: #2196F3;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    line-height: 1.4;
    transition: background-color .2s ease, transform .2s ease;
  }
  .btn-detail:hover {
    background-color: #1976D2;
    transform: translateY(-1px);
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
        <th class="status-toggle-header">表示 / 非表示</th>
        <th style="width: 50px;">詳細</th>
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
        <td class="status-toggle-cell">
          <form class="status-toggle-form" method="POST" action="toggle-content-status.php">
            <input type="hidden" name="content_id" value="<?php echo (int)$content['content_id']; ?>">
            <input type="hidden" name="redirect" value="list-content.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES, 'UTF-8') : ''; ?>">
            <input type="hidden" name="current_status" value="<?php echo (int)$content['indicate_flag']; ?>">
            <input type="hidden" name="target_status" value="<?php echo ((int)$content['indicate_flag'] === ContentModel::ACTIVE) ? ContentModel::INACTIVE : ContentModel::ACTIVE; ?>">
            <label class="status-toggle-label">
              <input
                type="checkbox"
                class="status-toggle-input"
                name="indicate_flag"
                value="<?php echo ((int)$content['indicate_flag'] === ContentModel::ACTIVE) ? ContentModel::INACTIVE : ContentModel::ACTIVE; ?>"
                <?php if((int)$content['indicate_flag'] === ContentModel::ACTIVE) echo ' checked="checked"'; ?>
                onchange="this.form.submit();"
              >
              <span class="status-toggle-slider"></span>
            </label>
            <span class="status-toggle-text <?php echo ((int)$content['indicate_flag'] === ContentModel::ACTIVE) ? 'active' : 'inactive'; ?>">
              <?php echo ((int)$content['indicate_flag'] === ContentModel::ACTIVE) ? '表示' : '非表示'; ?>
            </span>
          </form>
        </td>
        <td style="text-align:center;"><button type="button" class="btn-detail" onclick="location.href='edit-content.php?cont_id=<?php echo $content['content_id']; ?>'">詳細</button></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->

<?php if (!empty($toast_message)): ?>
<div class="toast-notice" id="toastNotice"><?php echo htmlspecialchars($toast_message, ENT_QUOTES, 'UTF-8'); ?></div>
<script>
(function(){
  var toast=document.getElementById('toastNotice');
  if(!toast)return;
  setTimeout(function(){toast.classList.add('show');},80);
  setTimeout(function(){toast.classList.remove('show');},3080);
})();
</script>
<style>
.toast-notice{position:fixed;left:20px;bottom:20px;padding:12px 20px;background:#4CAF50;color:#fff;border-radius:4px;box-shadow:0 2px 12px rgba(0,0,0,0.2);font-size:14px;opacity:0;transform:translateY(20px);transition:opacity .3s ease,transform .3s ease;z-index:9999;}
.toast-notice.show{opacity:1;transform:translateY(0);}
</style>
<?php endif; ?>

</body>
</html>