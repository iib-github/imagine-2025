<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('member') === false) {
    header("Location: ../login.php");
    exit;
  }

?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" >
<title>ご質問BOX送信完了 - THE Imagine Membersサイト</title>
<meta name="robots" content="noindex,nofollow" />
<meta name="viewport" content="width=device-width">
<link rel="apple-touch-icon" href="/membership/member/common/img/apple-touch-icon.png">
<link href="../common/css/main.css" rel="stylesheet">
<link href="../common/css/main.css" rel="stylesheet">
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<script src="../common/js/respond.min.js"></script>
<![endif]-->
<!--[if IE 6]><script src="../common/js/minmax.js"></script><![endif]-->
</head>

<body>
<section id="MV">
  <div class="Cnt">
    <h1><img src="../common/img/login_logo.png" width="320" alt="THE Imagine"/></h1>
  </div>
</section>
<div id="wrapper">
  <!-- Wrapper -->
  <div id="Contents">
    <!-- Contents -->
    <div id="Main">
      <!-- Main -->
      <section id="Document">
        <h2>ご質問BOX</h2>
        <div class="Block">
          <div class="comp-msg">
            <p>ご質問ありがとうございました！<br>ご質問内容を確認し、必要に応じてQ&Aライブやメールにてお答えさせていただきます。</p>
            <p>引き続き THE Imagineをお楽しみください。</p>
            <p>アストロクリエイター　星野ワタル</p>
            <a href="/membership/member/" class="btn">トップページに戻る</a>
          </div>
        </div>
      </section>
    </div>
    <!-- /Main -->
    <div id="Side">
      <!-- Side -->
      <?php include '../sidebar-contact.php'; ?>
    </div>
    <!-- /Side -->
  </div>
  <!-- /Contents -->
<?php include '../tmp/footer.php';?>
</div>
<!-- /Wrapper -->
<script src="../common/js/smoothscroll.js"></script>
</body>
</html>