<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/UploadModel.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/MemberModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('admin') === false) {
    header("Location: login.php");
    exit;
  }

  // 提出ファイル一覧取得
  $upload_model = new UploadModel();
  $upload_list = $upload_model->select(null, array('note_date' => BaseModel::ORDER_DESC));
  // 会員一覧取得
  $member_model = new MemberModel();
  $members = $member_model->select(null, array('member_id' => BaseModel::ORDER_DESC));

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>提出ファイル一覧 | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />
</head>

<body>

<!-- Wrapper starts -->
<div id="wrapper">
  <div class="InBox"><!-- INBOX -->
    <h1>更新情報一覧</h1>

  <?php
  $menu_active = 'upload';
  include_once 'menu.php';
  ?>

    <!-- <a href="edit-upload.php" class="regster">
      <div class="submenu Tab">新規登録</div>
    </a> -->
    <table class="member">
      <tr>
        <th>アップロードID</th>
        <th>日時</th>
        <th>会員名</th>
        <th>タイトル</th>
        <th>対象コース</th>
        <th>詳細</th>
        <th>ファイルリンク</th>
      </tr>

      <?php foreach ($upload_list as $n) : ?>
      <tr>
        <td><?php echo $n['upload_id']; ?></td>
        <td><?php echo htmlspecialchars(mb_substr($n['note_date'], 0, 10), ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php foreach ($members as $m) : if ($m['member_id']==$n['member_id']) { ?><?php echo htmlspecialchars($m['member_name'], ENT_QUOTES, 'UTF-8'); ?><?php } endforeach; ?></td>
        <td><?php echo htmlspecialchars($n['title'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td>
          <?php
            $target_course = !empty($n['target_course']) ? $n['target_course'] : 'all';
            switch($target_course) {
              case 'basic':
                echo 'ベーシック';
                break;
              case 'advance':
                echo 'アドバンス';
                break;
              case 'all':
              default:
                echo '全コース';
                break;
            }
          ?>
        </td>
        <td><?php echo htmlspecialchars($n['note'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td style="text-align:center"><input type="button" value="確認する" onclick="location.href='../<?php echo $n['path']; ?>'"></td>
      </tr>
      <?php endforeach; ?>

    </table>
  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->

</body>
</html>