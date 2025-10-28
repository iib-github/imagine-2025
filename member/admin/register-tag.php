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

// タグ登録時
if($_SERVER["REQUEST_METHOD"] == "POST") {
  // バリデーション
  if(empty($_POST['tag_name'])) {
    $errors[] = 'タグ名は必須です。';
  } elseif(strlen($_POST['tag_name']) > 50) {
    $errors[] = 'タグ名は50文字以内で入力してください。';
  } elseif($tag_model->existsTagName($_POST['tag_name'])) {
    $errors[] = 'このタグ名は既に使用されています。';
  }

  if(!empty($_POST['tag_description']) && strlen($_POST['tag_description']) > 500) {
    $errors[] = 'タグの説明は500文字以内で入力してください。';
  }

  // エラーがなければ登録
  if(empty($errors)) {
    $tag_data = array(
      'tag_name' => $_POST['tag_name'],
      'tag_description' => $_POST['tag_description'],
      'created_date' => date('Y-m-d H:i:s'),
      'modified_date' => date('Y-m-d H:i:s')
    );

    $success = $tag_model->registerTag($tag_data);
    if($success) {
      header("Location: list-tag.php?message=registered");
      exit;
    } else {
      $errors[] = 'タグの登録に失敗しました。';
    }
  }
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>タグ登録 | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />
</head>

<body>
<!-- Wrapper starts -->
<div id="wrapper">
  <div class="InBox"><!-- INBOX -->
    <h1>タグ登録</h1>

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

    <form method="POST" action="register-tag.php">
      <p><input type="submit" id="btnRegister" class="Btn" value="登録" name="register"></p>

      <table class="member">
        <tr>
          <th>名前</th>
          <td>
            <input type="text" name="tag_name" style="width:400px;" maxlength="50" value="<?php echo isset($_POST['tag_name']) ? htmlspecialchars($_POST['tag_name']) : ''; ?>">
            <span style="font-size: 12px; color: #999;">※必須、50文字以内</span>
          </td>
        </tr>
        <tr>
          <th>説明</th>
          <td>
            <textarea name="tag_description" style="width:600px;height:100px;"><?php echo isset($_POST['tag_description']) ? htmlspecialchars($_POST['tag_description']) : ''; ?></textarea>
            <span style="font-size: 12px; color: #999;">※500文字以内</span>
          </td>
        </tr>
      </table>
      <p><input type="submit" id="btnRegisterBottom" class="Btn" value="登録" name="register"></p>
      <p><input type="button" class="Btn" value="一覧に戻る" onclick="location.href='list-tag.php'"></p>
    </form>
  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->

</body>
</html>
