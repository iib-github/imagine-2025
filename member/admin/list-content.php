<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/CategoryModel.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/ContentModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('admin') === false) {
    header("Location: login.php");
    exit;
  }

  // コンテンツ一覧取得
  $content_model = new ContentModel();
  $content_list = $content_model->select(null, array('content_id' => BaseModel::ORDER_ASC));

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
    <table class="member">
      <tr>
        <th>コンテンツID</th>
        <th>カテゴリー</th>
        <th>Week</th>
        <th>タイトル</th>
        <th>公開日</th>
        <th>詳細</th>
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