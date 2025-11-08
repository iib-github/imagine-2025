<?php
require_once dirname(__FILE__) . '/../scripts/Session.class.php';
require_once dirname(__FILE__) . '/../scripts/model/TagModel.class.php';

$session = Session::getInstance();

// セッションがなければログイン画面に遷移させる。
if($session->get('admin') === false) {
  header("Location: login.php");
  exit;
}

$tag_model = new TagModel();

// 削除処理
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'delete') {
  if(isset($_POST['tag_id'])) {
    $result = $tag_model->deleteTag($_POST['tag_id']);
    if($result) {
      header("Location: list-tag.php?status=deleted");
      exit;
    }
  }
}

$toast_message = '';
if (isset($_GET['status'])) {
  switch ($_GET['status']) {
    case 'created':
      $toast_message = 'タグを登録しました。';
      break;
    case 'updated':
      $toast_message = 'タグを更新しました。';
      break;
    case 'deleted':
      $toast_message = 'タグを削除しました。';
      break;
  }
}

// タグ一覧を取得
$tag_list = $tag_model->getTagList(null, array('tag_id' => BaseModel::ORDER_ASC));

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>タグ一覧 | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />
<style>
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
</head>

<body>
<!-- Wrapper starts -->
<div id="wrapper">
  <div class="InBox"><!-- INBOX -->
    <h1>タグ一覧</h1>

<?php
  $menu_active = 'tags';
  include_once 'menu.php';
?>

    <a href="register-tag.php" class="regster">
      <div class="submenu Tab">＋ 新しいタグを追加</div>
    </a>

    <table class="member">
      <thead>
        <tr>
          <th style="width: 30px;">ID</th>
          <th>タグ名</th>
          <th>説明</th>
          <th style="width: 40px;">使用数</th>
          <th style="width: 50px;">操作</th>
        </tr>
      </thead>
      <tbody>
<?php
  if(empty($tag_list)) {
    echo '<tr><td colspan="5" style="text-align: center; padding: 20px;">タグが登録されていません。</td></tr>';
  } else {
    foreach($tag_list as $tag) {
      $use_count = $tag_model->getTagUseCount($tag['tag_id']);
      echo '<tr>';
      echo '<td>' . htmlspecialchars($tag['tag_id']) . '</td>';
      echo '<td>' . htmlspecialchars($tag['tag_name']) . '</td>';
      echo '<td>' . (empty($tag['tag_description']) ? '-' : htmlspecialchars(substr($tag['tag_description'], 0, 50))) . (strlen($tag['tag_description']) > 50 ? '...' : '') . '</td>';
      echo '<td>' . $use_count . '</td>';
      echo '<td style="text-align:center"><button type="button" class="btn-detail" onclick="location.href=\'edit-tag.php?tag_id=' . $tag['tag_id'] . '\'">詳細</button></td>';
      echo '</tr>';
    }
  }
?>
      </tbody>
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
