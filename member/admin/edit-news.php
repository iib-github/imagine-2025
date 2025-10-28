<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/NewsModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('admin') === false) {
    header("Location: login.php");
    exit;
  }

  $news_model = new NewsModel();
  $news_id = (int)@$_GET['n_id'];
  $news = $news_model->getNewsById($news_id);

  if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(empty($_POST['news_id'])) {
      // 新規登録時
      $insert_data = array(
        'note_date' => $_POST['note_date'],
        'description' => $_POST['description'],
        'text' => $_POST['text'],
        'is_active' => $_POST['active'],
      );
      $news_model->insert($insert_data);
    } else {
      // 更新時
      $update_data = array(
        'note_date' => $_POST['note_date'],
        'description' => $_POST['description'],
        'text' => $_POST['text'],
        'is_active' => $_POST['active'],
      );
      $news_model->update($update_data, array('id'=>$_POST['news_id']));
    }
    header("Location: list-news.php");
    exit;
  }

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>更新情報詳細 | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />
</head>

<body>
<!-- Wrapper starts -->
<div id="wrapper">
  <div class="InBox"><!-- INBOX -->
    <h1>更新情報詳細</h1>
  <?php
  $menu_active = 'news';
  include_once 'menu.php';
  ?>
    <form method="POST" action="edit-news.php" enctype="multipart/form-data">
      <?php if(!empty($news)): ?>
      <p><input type="submit" id="btnUpdate" class="Btn" value="更新" name="update"></p>
      <?php else: ?>
      <p><input type="submit" id="btnRegister" class="Btn" value="登録" name="register"></p>
      <?php endif; ?>
      <input type="hidden" name="news_id" value="<?php echo $news["id"]; ?>">
      <table class="member">
        <tr>
          <th>お知らせ日時</th>
          <td><input type="text" name="note_date" style="width:200px;" value="<?php echo htmlspecialchars($news["note_date"], ENT_QUOTES, 'UTF-8'); ?>">　例)「2017.06.05-15:00」という形式で入力してください。</td>
        </tr>
        <tr>
          <th>お知らせタイトル</th>
          <td><input type="text" name="description" style="width:800px;" value="<?php echo htmlspecialchars($news["description"], ENT_QUOTES, 'UTF-8'); ?>"></td>
        </tr>
        <tr>
          <th>お知らせ詳細</th>
          <td><textarea name="text" id="colume" style="width:800px;height:100px;"><?php echo htmlspecialchars($news["text"], ENT_QUOTES, 'UTF-8'); ?></textarea></td>
        </tr>
        <tr>
          <th>表示 / 非表示</th>
          <td>
            <select name="active">
              <option value="1"<?php if($news['is_active'] == 1) echo ' selected="selected"';?>>表示</option>
              <option value="0"<?php if($news['is_active'] == 0) echo ' selected="selected"';?>>非表示</option>
            </select>
          </td>
        </tr>
      </table>
    </form>
  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->

</body>
</html>