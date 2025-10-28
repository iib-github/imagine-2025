<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/NewsModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('admin') === false) {
    header("Location: login.php");
    exit;
  }

  // ニュース一覧取得
  $news_model = new NewsModel();
  $news_list = $news_model->select(null, array('note_date' => BaseModel::ORDER_DESC));

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>更新情報一覧 | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />
</head>

<body>

<!-- Wrapper starts -->
<div id="wrapper">
  <div class="InBox"><!-- INBOX -->
    <h1>更新情報一覧</h1>

  <?php
  $menu_active = 'news';
  include_once 'menu.php';
  ?>

    <a href="edit-news.php" class="regster">
      <div class="submenu Tab">新規登録</div>
    </a>
    <table class="member">
      <tr>
        <th>更新情報ID</th>
        <th>お知らせ日時(公開日時)</th>
        <th>タイトル</th>
        <th>表示 / 非表示</th>
        <th>詳細</th>
      </tr>

      <?php foreach ($news_list as $n) : ?>
      <tr>
        <td><?php echo $n['id']; ?></td>
        <td><?php echo htmlspecialchars(mb_substr($n['note_date'], 0, 10), ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php echo htmlspecialchars($n['description'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td>
          <?php
            if($n['is_active']) {
              echo '表示';
            } else {
              echo '非表示';
            }
          ?>
        </td>
        <td style="text-align:center"><input type="button" value="編集" onclick="location.href='edit-news.php?n_id=<?php echo $n['id']; ?>'"></td>
      </tr>
      <?php endforeach; ?>

    </table>
  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->

</body>
</html>