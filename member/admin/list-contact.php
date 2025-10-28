<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/ContactModel.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/MemberModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('admin') === false) {
    header("Location: login.php");
    exit;
  }

  // お知らせ一覧取得
  $contact_model = new ContactModel();
  $contact_list = $contact_model->select(null, array('created_date' => BaseModel::ORDER_DESC));

  // 会員一覧取得
  $member_model = new MemberModel();
  $members = $member_model->select(null, array('member_id' => BaseModel::ORDER_DESC));

  // カテゴリ表示名とidの紐付け表を作成
  $member_list = $member_model->select();
  $number_list = array();
  foreach ($member_list as $mem) {
    $number_list[$mem['member_id']] = $mem['member_name'];
  }

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>ご質問一覧 | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />
</head>

<body>

<!-- Wrapper starts -->
<div id="wrapper">
  <div class="InBox"><!-- INBOX -->
    <h1>ご質問一覧</h1>

  <?php
  $menu_active = 'contact';
  include_once 'menu.php';
  ?>

    <table class="member">
      <tr>
        <th style="width:5%;">ID</th>
        <th style="width:10%;">ご質問日時</th>
        <th style="width:10%;">会員名</th>
        <th style="width:10%;">お名前<br>(ハンドルネーム)</th>
        <th style="width:65%;">ご質問内容</th>
      </tr>

      <?php foreach ($contact_list as $n) : ?>
      <tr>
        <td style="width:5%;"><?php echo $n['id']; ?></td>
        <td style="width:10%;"><?php echo htmlspecialchars(mb_substr($n['created_date'], 0, 10), ENT_QUOTES, 'UTF-8'); ?></td>
        <td style="width:10%;"><a href="/membership/member/admin/edit-member.php?mid=<?php echo $n['member_id']; ?>"><?php echo $number_list[$n['member_id']]; ?></a></td>
        <td style="width:10%;"><?php echo htmlspecialchars($n['category'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td style="width:65%;"><?php echo htmlspecialchars($n['text'], ENT_QUOTES, 'UTF-8'); ?></td>
      </tr>
      <?php endforeach; ?>

    </table>
  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->

</body>
</html>