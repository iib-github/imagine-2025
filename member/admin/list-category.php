<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/CategoryModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('admin') === false) {
    header("Location: login.php");
    exit;
  }

  // カテゴリ一覧取得
  $category_model = new CategoryModel();
  $category_list = $category_model->select(null, array('category_id' => BaseModel::ORDER_ASC));

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>カテゴリー一覧 | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />
</head>

<body>

<!-- Wrapper starts -->
<div id="wrapper">
  <div class="InBox"><!-- INBOX -->
    <h1>カテゴリー一覧</h1>

  <?php
  $menu_active = 'ctgr';
  include_once 'menu.php';
  ?>

    <a href="register-category.php" class="regster">
      <div class="submenu Tab">新規登録</div>
    </a>
    <table class="member">
      <tr>
        <th style="width: 30px;">ID</th>
        <th>ナンバー</th>
        <th>タイトル</th>
        <th>対象コース</th>
        <th>公開日</th>
        <th style="width: 30px;">詳細</th>
      </tr>

<?php foreach ($category_list as $category) : ?>
      <tr>
        <td><?php echo $category['category_id']; ?></td>
<?php if ($category['category_number'] == '12'): ?>
        <td>イマジンラジオ</td>
<?php elseif ($category['category_number'] == '11'): ?>
        <td>QAライブ動画</td>
<?php else: ?>
        <td>Lesson<?php echo $category['category_number']; ?></td>
<?php endif; ?>
        <td><?php echo htmlspecialchars($category['category_title'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td>
          <?php
            $target_course = !empty($category['target_course']) ? $category['target_course'] : 'all';
            switch($target_course) {
              case 'basic':
                echo 'ベーシック';
                break;
              case 'advance':
                echo 'アドバンス';
                break;
              case 'all':
              default:
                echo 'アドバンス';
                break;
            }
          ?>
        </td>
        <td><?php echo htmlspecialchars($category["pub_date"], ENT_QUOTES, 'UTF-8'); ?></td>
        <td style="text-align:center"><input type="button" value="詳細" onclick="location.href='edit-category.php?ctg_id=<?php echo $category['category_id']; ?>'"></td>
      </tr>
<?php endforeach; ?>

    </table>
  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->

</body>
</html>