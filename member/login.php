<?php
  require_once dirname(__FILE__) . '/scripts/Session.class.php';
  require_once dirname(__FILE__) . '/scripts/model/MemberModel.class.php';
  $session = Session::getInstance();

  // ログイン済みであれば自動で遷移させる。
  if($session->get('member') === true) {
    header("Location: index.php");
    exit;
  }

  // メアドとパスワードがPOSTされた時
  if($_SERVER["REQUEST_METHOD"] == "POST") {
    // $session->destroy();
    //   header("location: login-expired.php");
    // exit;

    $mail = @$_POST["login_mail"];
    $pass = @$_POST["password"];

    $member_model = new MemberModel($member_id = NULL);
    $login_success = $member_model->login($mail, $pass);

    if($login_success) { // ログイン成功
      $member = $member_model->getMemberByMail($mail);
      $session->set('member', $member['member_id']);
      header("location: index.php");
      exit;
    } else { // ログイン失敗
      $session->destroy();
      // header("location: login-fail.php");
      header("location: login-expired.php");
      exit;
    }
  }
  // $session->destroy();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" >
<title>THE Imagine Membersログイン</title>
<meta name="robots" content="noindex,nofollow" />
<meta name="viewport" content="width=device-width">
<link rel="apple-touch-icon" href="/common/img/apple-touch-icon.png">
<link href="common/css/main.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<script src="common/js/respond.min.js"></script>
<![endif]-->
<!--[if IE 6]><script src="common/js/minmax.js"></script><![endif]-->
<script>
  function submit(){
    document.getElementById("frm").submit();
  }
</script>
<?php include 'tmp/analytics.php';?>
</head>
<body class="LoginPage">
<section id="LoginBox">
  <div class="Cnt">
    <h1><img src="common/img/login_logo.png" width="316" alt="THE Imagine Members"/></h1>
    <form method="POST" action="login.php" id="frm">
      <label>
        <input type="text" name="login_mail" placeholder="ID" class="UserInput">
      </label>
      <label>
        <input type="password" name="password" placeholder="PASSWORD" class="PassInput">
      </label>
      <p class="login"><input type="button" value="LOGIN" onclick="document.getElementById('frm').submit();"></p>
    </form>
  </div>
</section>

<script type="text/javascript">
// Enterボタンでログイン
$(".UserInput, .PassInput").keypress(function(e) {
  if(e.which == 13) {
    $("#frm").submit();
  }
});

</script>
</body>
</html>