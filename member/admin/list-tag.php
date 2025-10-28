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
      header("Location: list-tag.php?message=deleted");
      exit;
    }
  }
}

// タグ一覧を取得
$tag_list = $tag_model->getTagList(null, array('tag_name' => BaseModel::ORDER_ASC));

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>タグ一覧 | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />
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

    <a href="register-tag.php" class="regster">＋ 新しいタグを追加</a>

<?php
  if(isset($_GET['message']) && $_GET['message'] === 'deleted') {
    echo '<div style="background-color: #e8f5e9; border: 1px solid #4caf50; color: #2e7d32; padding: 10px; margin-bottom: 20px; border-radius: 3px;">タグを削除しました。</div>';
  }
  if(isset($_GET['message']) && $_GET['message'] === 'registered') {
    echo '<div style="background-color: #e8f5e9; border: 1px solid #4caf50; color: #2e7d32; padding: 10px; margin-bottom: 20px; border-radius: 3px;">タグを登録しました。</div>';
  }
  if(isset($_GET['message']) && $_GET['message'] === 'updated') {
    echo '<div style="background-color: #e8f5e9; border: 1px solid #4caf50; color: #2e7d32; padding: 10px; margin-bottom: 20px; border-radius: 3px;">タグを更新しました。</div>';
  }
?>

    <table class="member">
      <thead>
        <tr>
          <th>ID</th>
          <th>タグ名</th>
          <th>説明</th>
          <th>使用数</th>
          <th>操作</th>
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
      echo '<td style="text-align:center"><input type="button" value="詳細" onclick="location.href=\'edit-tag.php?tag_id=' . $tag['tag_id'] . '\'"></td>';
      echo '</tr>';
    }
  }
?>
      </tbody>
    </table>
  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->

</body>
</html>
