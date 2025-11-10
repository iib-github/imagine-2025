<?php
  require_once dirname(__FILE__) . '/scripts/env.php';
  require_once dirname(__FILE__) . '/scripts/Session.class.php';
  require_once dirname(__FILE__) . '/scripts/model/MemberModel.class.php';
  
  // .envファイルを読み込む
  loadEnv();
  
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
<title>会員サイト利用方法 - THE Imagine Membersサイト</title>
<meta name="robots" content="noindex,nofollow" />
<meta name="viewport" content="width=device-width">
<link rel="apple-touch-icon" href="/common/img/apple-touch-icon.png">
<link href="common/css/main.css" rel="stylesheet">
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
    background: url('common/img/bg01.png') center/cover no-repeat;
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
<script src="common/js/respond.min.js"></script>
<![endif]-->
<!--[if IE 6]><script src="common/js/minmax.js"></script><![endif]-->
<?php include 'tmp/analytics.php';?>
</head>

<body>
<section id="MV">
  <div class="mv-video-wrapper">
    <video id="mvVideo" autoplay muted loop playsinline preload="auto" poster="common/img/bg01.png">
      <source src="common/video/mv.mp4" type="video/mp4">
    </video>
  </div>
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
        <h2>会員サイト利用方法</h2>
        <div class="Block">
<h3>▶「この授業を完了にする」について</h3>
<p class="midNote">各コンテンツにある「この授業を完了にする」をクリック後も動画を視聴することができます。<br>
また、「この授業を完了にする」をクリックすることで各期間の達成度をあげることができます。</p>

<h3>▶ 達成度について</h3>
<p>毎週配信される動画を見て、出された課題を達成したタイミングで、「この授業を完了にする」ボタンを押してください。<br>
<span class="small">※動画を見ただけでは、課題の完了ではありません。</span></p>
<p class="midNote">毎月に配信される複数の動画を見て、課題をクリアすると達成度が100％となります。<br>
この達成度は、みなさんの達成度チェックにも使っていますので、実際に「課題を達成した！」段階でクリックしましょう！</p>

<h3>▶ 質問の受付について</h3>
<p class="midNote">メニューにある「ご質問BOX」から課題に対する質問や感想・報告などを受けつけています。</p>

<h3>▶ 登録情報の変更について</h3>
<p class="midNote">登録情報の変更、またログインID/パスワードをお忘れの方は[<?php echo env('MAIL_SUPPORT_ADDRESS', 'info@hoshino-wataru.com'); ?>]宛にメールをお送りください。</p>


<h3>▶ 推奨ブラウザについて</h3>
<p class="midNote">当サイトにおいての推奨ブラウザは、Microsoft Internet Explorer(version11以降)/Mozilla Firefox/Chrome/Safariとさせていただいております。<br>
<span class="small">※ご覧になりにくい場合は、各ブラウザを最新のものにバージョンアップして下さい。</span></p>

<h3>▶ 動画視聴環境について</h3>
<p>当サイトでは、動画配信手段として動画共有サービス「Vimeo」を使用しております。<br>
コンテンツのご視聴に最適なOS/CPU等の環境は、Vimeoの推奨する環境に依存します。</p>
<p class="midNote"><span class="small">※推奨のネットワーク環境でも、ご利用のパソコンの処理速度が遅い場合や、メモリー容量が不足しているなどの場合には、動画・音声が正常に再生できないか、または音声は聞こえるけれども動画が正しく表示されないといった不具合が生じることがあります。さらに企業などの社内LAN環境では動画コンテンツの再生に制限をかけている場合があり、動画付きのコンテンツが再生できない場合があります。</span></p>


        </div>
      </section>
    </div>
    <!-- /Main -->
    <div id="Side">
      <!-- Side -->
      <?php include 'sidebar.php'; ?>
    </div>
    <!-- /Side -->
  </div>
  <!-- /Contents -->
<?php include 'tmp/footer.php';?>
</div>
<!-- /Wrapper -->
<script src="common/js/smoothscroll.js"></script>
<script>
  (function() {
    var video = document.getElementById('mvVideo');
    if (!video) {
      return;
    }

    var wrapper = video.parentNode;
    var fallback = function() {
      if (!video) return;
      video.pause();
      video.removeAttribute('src');
      video.load();
      video.style.display = 'none';
      video.style.pointerEvents = 'none';
      if (wrapper && wrapper.classList.contains('mv-video-wrapper')) {
        wrapper.style.backgroundImage = "url('common/img/bg01.png')";
        wrapper.style.backgroundSize = 'cover';
        wrapper.style.backgroundPosition = 'center';
        wrapper.classList.add('mv-video-fallback');
      }
    };

    var userAgent = window.navigator.userAgent.toLowerCase();
    if (/firefox\/[0-9]+\./.test(userAgent)) {
      fallback();
      return;
    }

    video.addEventListener('error', fallback);
    video.addEventListener('stalled', fallback);
    video.addEventListener('emptied', fallback);

    var playPromise = video.play();
    if (playPromise && typeof playPromise.catch === 'function') {
      playPromise.catch(function() {
        fallback();
      });
    }
  })();
</script>
</body>
</html>