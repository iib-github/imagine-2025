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
<link rel="apple-touch-icon" href="/common/img/apple-touch-icon.png">
<link href="../common/css/main.css" rel="stylesheet">
<link href="../common/css/main.css" rel="stylesheet">
<style>
  #MV {
    position: relative;
    overflow: hidden;
  }
  .mv-video-wrapper {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    background: url('../common/img/bg01.png') center/cover no-repeat;
  }
  .mv-video-wrapper video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: 0;
  }
  #MV .Cnt {
    position: relative;
    z-index: 1;
  }
</style>
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<script src="../common/js/respond.min.js"></script>
<![endif]-->
<!--[if IE 6]><script src="../common/js/minmax.js"></script><![endif]-->
</head>

<body>
<section id="MV">
  <div class="mv-video-wrapper">
    <video id="mvVideo" autoplay muted loop playsinline preload="auto" poster="../common/img/bg01.png">
      <source src="../common/video/mv.mp4" type="video/mp4">
    </video>
  </div>
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
            <p>アストロクリエイター 星野ワタル</p>
            <a href="/" class="btn">トップページに戻る</a>
          </div>
        </div>
      </section>
    </div>
    <!-- /Main -->
    <div id="Side">
      <!-- Side -->
      <?php include '../sidebar.php'; ?>
    </div>
    <!-- /Side -->
  </div>
  <!-- /Contents -->
<?php include '../tmp/footer.php';?>
</div>
<!-- /Wrapper -->
<script src="../common/js/smoothscroll.js"></script>
<script>
  (function() {
    var video = document.getElementById('mvVideo');
    if (!video) {
      return;
    }
    var userAgent = window.navigator.userAgent.toLowerCase();
    if (/firefox\/[0-9]+\./.test(userAgent)) {
      video.style.display = 'none';
      var wrapper = video.parentNode;
      if (wrapper && wrapper.classList.contains('mv-video-wrapper')) {
        wrapper.style.backgroundImage = "url('../common/img/bg01.png')";
        wrapper.style.backgroundSize = 'cover';
        wrapper.style.backgroundPosition = 'center';
      }
      return;
    }
    video.addEventListener('error', function() {
      video.style.display = 'none';
    });
    var playPromise = video.play();
    if (playPromise !== undefined) {
      playPromise.catch(function() {
        video.style.display = 'none';
        video.style.pointerEvents = 'none';
      });
    }
  })();
</script>
</body>
</html>