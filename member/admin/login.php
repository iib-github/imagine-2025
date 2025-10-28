<?php
  //// 管理画面アカウント
  define('ADMIN_ID', 'adm1n');
  define('ADMIN_PW', 'm7ufzanb');
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';

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

    if($id === ADMIN_ID && $pass == ADMIN_PW) {
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