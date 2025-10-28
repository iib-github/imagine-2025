<?php
  require_once dirname(__FILE__) . '/../scripts/env.php';
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  
  // .envファイルを読み込む
  loadEnv();
  
  //// 管理画面アカウント（環境変数から取得）
  $admin_id = env('ADMIN_ID');
  $admin_pw = env('ADMIN_PW');
  
  if (empty($admin_id) || empty($admin_pw)) {
    die('管理画面の設定が正しくありません。.envファイルを確認してください。');
  }

  $session = Session::getInstance();

  // ログイン済みであれば自動で遷移させる。
  if($session->get('admin') === true) {
    header("Location: list-member.php");
    exit;
  }

  // IDとパスワードがPOSTされた時
  if($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = @$_POST["ID"];
    $pass = @$_POST["PASS"];

    if($id === $admin_id && $pass == $admin_pw) {
      $session->set('admin', true);
      header("location: list-member.php");
      exit;
    } else {
      $is_fail = true;
    }
  }

  $session->destroy();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>ログイン | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />
<script>
  function submit(){
    document.getElementById("frmLogin").submit();
  }
</script>
</head>

<body>

<!-- Wrapper starts -->
<div id="wrapper">


<div class="LoginBox">
<h1>ADMIN LOGIN</h1>
<form method="post" action="login.php" id="frmLogin">
<table>
<tr><th>ID:</th><td><input type="text" size="20" name="ID" value="" /></td></tr>
<tr><th>PASS:</th><td><input type="password" size="20" name="PASS" value="" /></td></tr>
</table>

<p class="send">
<input type="button" class="Btn" value="LOG IN" onclick="submit();">
</p>
</form>
</div>


</div>
<!-- Wrapper ends -->


</body>
</html>