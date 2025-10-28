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
$errors = array();
$tag = null;

// 編集対象のタグ取得
if($_SERVER["REQUEST_METHOD"] == "GET") {
  if(isset($_GET['tag_id'])) {
    $tag = $tag_model->getTagById($_GET['tag_id']);
    
    // DBからタグが取れなければ一覧画面に飛ばす。
    if(empty($tag)) {
      header("Location: list-tag.php");
      exit;
    }
  } else {
    header("Location: list-tag.php");
    exit;
  }
} else { // POST時
  // バリデーション
  if(empty($_POST['tag_name'])) {
    $errors[] = 'タグ名は必須です。';
  } elseif(strlen($_POST['tag_name']) > 50) {
    $errors[] = 'タグ名は50文字以内で入力してください。';
  }

  if(!empty($_POST['tag_description']) && strlen($_POST['tag_description']) > 500) {
    $errors[] = 'タグの説明は500文字以内で入力してください。';
  }

  // 名前の重複チェック（自分自身は除外）
  $existing_tag = $tag_model->getTagByName($_POST['tag_name']);
  if(!empty($existing_tag) && $existing_tag['tag_id'] != $_POST['tag_id']) {
    $errors[] = 'このタグ名は既に使用されています。';
  }

  // エラーがなければ更新
  if(empty($errors)) {
    $update_data = array(
      'tag_name' => $_POST['tag_name'],
      'tag_description' => $_POST['tag_description'],
      'modified_date' => date('Y-m-d H:i:s')
    );

    $where_data = array('tag_id' => $_POST['tag_id']);
    $success = $tag_model->updateTag($update_data, $where_data);
    
    if($success) {
      header("Location: list-tag.php?message=updated");
      exit;
    } else {
      $errors[] = 'タグの更新に失敗しました。';
    }
  }
  
  // POST時の表示用に取得
  $tag = array(
    'tag_id' => $_POST['tag_id'],
    'tag_name' => $_POST['tag_name'],
    'tag_description' => $_POST['tag_description']
  );
}

// 使用回数を取得
$use_count = $tag_model->getTagUseCount($tag['tag_id']);

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>タグ編集 | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />
</head>

<body>
<!-- Wrapper starts -->
<div id="wrapper">
  <div class="InBox"><!-- INBOX -->
    <h1>タグ編集</h1>

<?php
  $menu_active = 'tags';
  include_once 'menu.php';
?>

<?php
  if(!empty($errors)) {
    echo '<div class="error-list"><ul>';
    foreach($errors as $error) {
      echo '<li>' . htmlspecialchars($error) . '</li>';
    }
    echo '</ul></div>';
  }
?>

    <p><input type="submit" id="btnUpdate" class="Btn" value="更新" name="update"></p>
    <input type="hidden" name="tag_id" value="<?php echo htmlspecialchars($tag['tag_id']); ?>">
    <table class="member">
      <tr>
        <th style="width: 150px;">タグID</th>
        <td><?php echo htmlspecialchars($tag['tag_id']); ?></td>
      </tr>
      <tr>
        <th>タグ名</th>
        <td>
          <input type="text" name="tag_name" style="width: 400px;" maxlength="50" value="<?php echo htmlspecialchars($tag['tag_name']); ?>">
          <span style="font-size: 12px; color: #999;">※必須、50文字以内</span>
        </td>
      </tr>
      <tr>
        <th>説明</th>
        <td>
          <textarea name="tag_description" style="width: 600px; height: 100px;"><?php echo htmlspecialchars($tag['tag_description']); ?></textarea>
          <span style="font-size: 12px; color: #999;">※500文字以内</span>
        </td>
      </tr>
      <tr>
        <th>使用中のコンテンツ数</th>
        <td><?php echo $use_count; ?> 件</td>
      </tr>
    </table>
    <p><input type="submit" id="btnUpdateBottom" class="Btn" value="更新" name="update"></p>
    <p><input type="button" class="Btn" value="一覧に戻る" onclick="location.href='list-tag.php'"></p>
  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->

</body>
</html>
