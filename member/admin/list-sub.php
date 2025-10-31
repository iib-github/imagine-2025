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

  // コンテンツ一覧取得
  $sub_model = new SubModel();
  $sub_list = $sub_model->select(null, array('sub_id' => BaseModel::ORDER_ASC));

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
<title>資料一覧 | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />
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
        <th style="width: 30px;">詳細</th>
      </tr>
      <?php foreach ($sub_list as $sub) : ?>
      <tr>
        <td><?php echo $sub['sub_id']; ?></td>
        <td>Lesson<?php echo $number_list[$sub['category_id']]; ?></td>
        <td><?php echo htmlspecialchars($sub['content_title'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td>
          <?php
            $target_course = !empty($sub['target_course']) ? $sub['target_course'] : 'all';
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
        <td><?php echo htmlspecialchars($sub["pub_date"], ENT_QUOTES, 'UTF-8'); ?></td>
        <td style="text-align:center"><input type="button" value="詳細" onclick="location.href='edit-sub.php?sub_id=<?php echo $sub['sub_id']; ?>'"></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->

</body>
</html>