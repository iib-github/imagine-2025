<?php
  //一旦非表示
  header("Location: /");
  exit;

  require_once dirname(__FILE__) . '/scripts/Session.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('member') === false) {
    header("Location: login.php");
    exit;
  }

?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" >
<title>提出完了 - THE Imagine Membersサイト</title>
<meta name="robots" content="noindex,nofollow" />
<meta name="viewport" content="width=device-width">
<link rel="apple-touch-icon" href="/common/img/apple-touch-icon.png">
<link href="common/css/main.css" rel="stylesheet">
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<script src="common/js/respond.min.js"></script>
<![endif]-->
<!--[if IE 6]><script src="common/js/minmax.js"></script><![endif]-->
<?php include 'tmp/analytics.php';?>
</head>

<body>
<section id="MV">
  <div class="Cnt">
    <h1><img src="common/img/login_logo.png" width="320" alt="THE Imagine"/></h1>
  </div>
</section>
<div id="wrapper">
  <!-- Wrapper -->
  <div id="Contents">
    <!-- Contents -->
    <div id="Main">
      <!-- Main -->
      <section id="Document">
        <h2>ファイル提出完了</h2>
        <div class="Block">
          <p style="margin-bottom:20px;">選択したファイルの提出が完了しました。<br>
          提出ありがとうございました！</p>
          <p>星野ワタル</p>
        </div>
      </section>
    </div>
    <!-- /Main -->
    <div id="Side">
      <?php include 'sidebar.php'; ?>
    </div>
  </div>
  <!-- /Contents -->
<?php include 'tmp/footer.php';?>
</div>
<!-- /Wrapper -->
<script src="common/js/smoothscroll.js"></script>
</body>
</html>