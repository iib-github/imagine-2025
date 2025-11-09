<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/CategoryModel.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/SubModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('admin') === false) {
    header("Location: login.php");
    exit;
  }

  $toast_message = '';
  if (isset($_GET['status'])) {
    switch ($_GET['status']) {
      case 'created':
        $toast_message = '資料を登録しました。';
        break;
      case 'updated':
        $toast_message = '資料を更新しました。';
        break;
      case 'deleted':
        $toast_message = '資料を削除しました。';
        break;
      case 'toggled':
        $toast_message = '表示状態を更新しました。';
        break;
    }
  }

  // コンテンツ一覧取得
  $sub_model = new SubModel();
  $sub_list = $sub_model->select(null, array('sub_id' => BaseModel::ORDER_ASC));

  // カテゴリ表示名とidの紐付け表を作成
  $category_model = new CategoryModel();
  $category_list = $category_model->select();
  $category_map = array();
  foreach ($category_list as $ctg) {
    $label = !empty($ctg['category_title'])
      ? $ctg['category_title']
      : 'Lesson' . $ctg['category_number'];
    $category_map[$ctg['category_id']] = $label;
  }

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>資料一覧 | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />
<style>
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
    width: 90px;
  }
  .course-label {
    display:inline-block;
    padding:2px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:bold;
    color:#fff;
  }
  .course-label--advance { background-color:#00695c; }
  .course-label--basic { background-color:#1e88e5; }
  .course-label--all { background-color:#8e24aa; }
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
    <h1>資料一覧</h1>

<?php
  $menu_active = 'sub';
  include_once 'menu.php';
?>

    <a href="register-sub.php" class="regster">
      <div class="submenu Tab">新規登録</div>
    </a>
    <table class="member">
      <tr>
        <th style="width: 30px;">ID</th>
        <th>カテゴリー</th>
        <th>タイトル</th>
        <th>対象コース</th>
        <th>公開日</th>
        <th class="status-toggle-header">表示 / 非表示</th>
        <th style="width: 50px;">詳細</th>
      </tr>
      <?php foreach ($sub_list as $sub) : ?>
      <tr>
        <td><?php echo $sub['sub_id']; ?></td>
        <td><?php echo htmlspecialchars(isset($category_map[$sub['category_id']]) ? $category_map[$sub['category_id']] : '未設定', ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php echo htmlspecialchars($sub['content_title'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td>
          <?php
            $target_course = !empty($sub['target_course']) ? $sub['target_course'] : 'advance';
            $course_name = 'アドバンス';
            $course_class = 'course-label course-label--advance';
            switch($target_course) {
              case 'basic':
                $course_name = 'ベーシック';
                $course_class = 'course-label course-label--basic';
                break;
              case 'all':
                $course_name = '全体';
                $course_class = 'course-label course-label--all';
                break;
              case 'advance':
              default:
                $course_name = 'アドバンス';
                $course_class = 'course-label course-label--advance';
                break;
            }
          ?>
          <span class="<?php echo $course_class; ?>"><?php echo htmlspecialchars($course_name, ENT_QUOTES, 'UTF-8'); ?></span>
        </td>
        <td><?php echo htmlspecialchars($sub["pub_date"], ENT_QUOTES, 'UTF-8'); ?></td>
        <td class="status-toggle-cell">
          <form class="status-toggle-form" method="POST" action="toggle-sub-status.php">
            <input type="hidden" name="sub_id" value="<?php echo (int)$sub['sub_id']; ?>">
            <input type="hidden" name="redirect" value="list-sub.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES, 'UTF-8') : ''; ?>">
            <input type="hidden" name="target_status" value="<?php echo ((int)$sub['indicate_flag'] === SubModel::ACTIVE) ? SubModel::INACTIVE : SubModel::ACTIVE; ?>">
            <label class="status-toggle-label">
              <input
                type="checkbox"
                class="status-toggle-input"
                name="indicate_flag"
                value="<?php echo ((int)$sub['indicate_flag'] === SubModel::ACTIVE) ? SubModel::INACTIVE : SubModel::ACTIVE; ?>"
                <?php if((int)$sub['indicate_flag'] === SubModel::ACTIVE) echo ' checked="checked"'; ?>
                onchange="this.form.submit();"
              >
              <span class="status-toggle-slider"></span>
            </label>
            <span class="status-toggle-text <?php echo ((int)$sub['indicate_flag'] === SubModel::ACTIVE) ? 'active' : 'inactive'; ?>">
              <?php echo ((int)$sub['indicate_flag'] === SubModel::ACTIVE) ? '表示' : '非表示'; ?>
            </span>
          </form>
        </td>
        <td style="text-align:center"><button type="button" class="btn-detail" onclick="location.href='edit-sub.php?sub_id=<?php echo $sub['sub_id']; ?>'">詳細</button></td>
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