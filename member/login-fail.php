<?php
  require_once dirname(__FILE__) . '/scripts/env.php';
  require_once dirname(__FILE__) . '/scripts/Session.class.php';
  
  // .envファイルを読み込む
  loadEnv();
  
  $session = Session::getInstance();

  // ログイン済みであれば自動で遷移させる。
  if($session->get('member') === true) {
    header("Location: index.php");
    exit;
  }

?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" >
<title>THE Imagine Membersログイン</title>
<meta name="robots" content="noindex,nofollow" />
<meta name="viewport" content="width=device-width">
<link rel="apple-touch-icon" href="/membership/member/common/img/apple-touch-icon.png">
<link href="common/css/main.css" rel="stylesheet">
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<script src="common/js/respond.min.js"></script>
<![endif]-->
<!--[if IE 6]><script src="common/js/minmax.js"></script><![endif]-->
<?php include 'tmp/analytics.php';?>
</head>

<body class="LoginPage">
  <section id="LoginBox">
    <div class="Cnt">
      <h1><img src="common/img/login_logo.png" width="316" alt="THE Imagine Members"/></h1>
      <p>ログインに失敗しました。<br>ID/Passwordが不明な場合は<a href="mailto:<?php echo env('MAIL_SUPPORT_ADDRESS', 'info@hoshino-wataru.com'); ?>">サポート</a>にお問い合わせください。</p>
      <a href="login.php"><p class="Btn-Back">ログイン画面へ</p></a>
    </div>
  </section>

</body>
</html>