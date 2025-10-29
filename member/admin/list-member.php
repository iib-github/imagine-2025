<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/MemberModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('admin') === false) {
    header("Location: login.php");
    exit;
  }

  // 会員一覧取得
  $member_model = new MemberModel();
  $members = $member_model->select(null, array('member_id' => BaseModel::ORDER_DESC));

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>会員一覧 | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />
<script type="text/javascript">
  function sendAccInfo(id, name, mail, password) {
    var msg =
      "以下の内容を送信します。\n\n" +
      "名前：" + name + "\n" +
      "ログインメールアドレス：" + mail + "\n" +
      "ログインパスワード：" + password;

    if(window.confirm(msg)) {
      location.href = "send-accinfo.php?mid=" + id;
    }
  }
</script>

</head>

<body>

<!-- Wrapper starts -->
<div id="wrapper">
  <div class="InBox"><!-- INBOX -->
    <h1>会員一覧</h1>

<?php
  $menu_active = 'cstm';
  include_once 'menu.php';
?>

    <a href="register-member.php" class="regster">
      <div class="submenu Tab">新規登録</div>
    </a>
    <table class="member">
      <tr>
        <th style="width: 30px;">ID</th>
        <th>名前</th>
        <th>コース</th>
        <th>ログインメールアドレス</th>
        <th style="width: 30px;">詳細</th>
        <!--th>アカウント連絡</th-->
      </tr>

      <?php foreach ($members as $member) : ?>
      <tr>
        <td><?php echo $member['member_id']; ?></td>
        <td><?php echo htmlspecialchars($member['member_name'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php
        if ($member['select_course'] == 1) {
          echo 'アドバンス';
        } elseif ($member['select_course'] == 2) {
          echo 'ベーシック';
        } elseif ($member['select_course'] == 3) {
          echo 'その他';
        }
        ?></td>
        <td><?php echo htmlspecialchars($member['login_mail'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td style="text-align:center"><input type="button" value="詳細" onclick="location.href='edit-member.php?mid=<?php echo $member['member_id']; ?>'"></td>
        <!--td>

          <?php //if($member['is_contacted'] == 1) : ?>
          <input type="button" value="メール送信" onclick="sendAccInfo(<?php //echo $member['member_id']; ?>, '<?php //echo htmlspecialchars($member['member_name'], ENT_QUOTES, 'UTF-8'); ?>', '<?php //echo htmlspecialchars($member['login_mail'], ENT_QUOTES, 'UTF-8'); ?>', '<?php //echo htmlspecialchars($member['login_password'], ENT_QUOTES, 'UTF-8'); ?>');">
          <?php //else: ?>
          <p>送信済み</p>
          <?php //endif; ?>
        </td-->
      </tr>
      <?php endforeach; ?>

    </table>
  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->
</body>
</html>