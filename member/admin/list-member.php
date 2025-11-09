<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/MemberModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('admin') === false) {
    header("Location: login.php");
    exit;
  }

  $toast_message = '';
  if (isset($_GET['status'])) {
    switch ($_GET['status']) {
      case 'created':
        $toast_message = '会員を登録しました。';
        break;
      case 'updated':
        $toast_message = '会員情報を更新しました。';
        break;
      case 'deleted':
        $toast_message = '会員を削除しました。';
        break;
      case 'imported':
        $toast_message = '会員情報をインポートしました。';
        break;
    }
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
<style>
  .course-label {
    display:inline-block;
    padding:2px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:bold;
    color:#fff;
  }
  .course-label--advance { background-color:#00695c; }
  .course-label--basic { background-color:#1e88e5; }
  .course-label--other { background-color:#757575; }
  .btn-detail {
    display: inline-block;
    padding: 6px 12px;
    background-color: #2196F3;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    line-height: 1.4;
    transition: background-color .2s ease, transform .2s ease;
  }
  .btn-detail:hover {
    background-color: #1976D2;
    transform: translateY(-1px);
  }
</style>
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
    <a href="import-members.php" class="regster" style="margin-left:10px;">
      <div class="submenu Tab">CSVインポート</div>
    </a>
    <table class="member">
      <tr>
        <th style="width: 30px;">ID</th>
        <th>名前</th>
        <th>コース</th>
        <th>ログインメールアドレス</th>
        <th style="width: 50px;">詳細</th>
        <!--th>アカウント連絡</th-->
      </tr>

      <?php foreach ($members as $member) : ?>
      <tr>
        <td><?php echo $member['member_id']; ?></td>
        <td><?php echo htmlspecialchars($member['member_name'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td>
          <?php
            $course_value = (int)$member['select_course'];
            $course_name = 'アドバンス';
            $course_class = 'course-label course-label--advance';
            if ($course_value === 2) {
              $course_name = 'ベーシック';
              $course_class = 'course-label course-label--basic';
            } elseif ($course_value === 3) {
              $course_name = 'その他';
              $course_class = 'course-label course-label--other';
            }
          ?>
          <span class="<?php echo $course_class; ?>"><?php echo htmlspecialchars($course_name, ENT_QUOTES, 'UTF-8'); ?></span>
        </td>
        <td><?php echo htmlspecialchars($member['login_mail'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td style="text-align:center"><button type="button" class="btn-detail" onclick="location.href='edit-member.php?mid=<?php echo $member['member_id']; ?>'">詳細</button></td>
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

<?php if (!empty($toast_message)): ?>
<div class="toast-notice" id="toastNotice"><?php echo htmlspecialchars($toast_message, ENT_QUOTES, 'UTF-8'); ?></div>
<script>
(function(){
  var toast=document.getElementById('toastNotice');
  if(!toast)return;
  setTimeout(function(){toast.classList.add('show');},80);
  setTimeout(function(){toast.classList.remove('show');},3080);
})();
</script>
<style>
.toast-notice{position:fixed;left:20px;bottom:20px;padding:12px 20px;background:#4CAF50;color:#fff;border-radius:4px;box-shadow:0 2px 12px rgba(0,0,0,0.2);font-size:14px;opacity:0;transform:translateY(20px);transition:opacity .3s ease,transform .3s ease;z-index:9999;}
.toast-notice.show{opacity:1;transform:translateY(0);}
</style>
<?php endif; ?>
</body>
</html>