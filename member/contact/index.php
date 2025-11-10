<?php
  require_once dirname(__FILE__) . '/../scripts/env.php';
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/send-inquiry.php';
  
  // .envファイルを読み込む
  loadEnv();
  
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('member') === false) {
    header("Location: ../login.php");
    exit;
  }

  $member_id = $session->get('member');
  $csrf_token = $session->getCsrfToken('contact_csrf');
  $errMsg = '';

  if($_SERVER["REQUEST_METHOD"] == "POST") {
    $posted_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    $handle_value = isset($_POST['f_handle']) ? trim($_POST['f_handle']) : '';
    $text_value = isset($_POST['f_text']) ? trim($_POST['f_text']) : '';

    if(!$session->validateCsrfToken($posted_token, 'contact_csrf')) {
      $errMsg = 'セッションの有効期限が切れました。もう一度お試しください。';
      $session->clear('contact_csrf');
      $csrf_token = $session->getCsrfToken('contact_csrf');
    } else {
      if(sendInquiry($session->get('member'), $handle_value, $text_value)) {
        $session->clear('contact_csrf');
        header("Location: complete.php");
        exit;
      } else {
        $errMsg = '送信に失敗しました。時間を置いて改めてお試しください。';
      }
    }
  }
  $form_handle_value = '';
  $form_text_value = '';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" >
<title>ご質問BOX - THE Imagine Membersサイト</title>
<meta name="robots" content="noindex,nofollow" />
<meta name="viewport" content="width=device-width">
<link rel="apple-touch-icon" href="/membership/member/common/img/apple-touch-icon.png">
<link href="../common/css/main.css" rel="stylesheet">
<link href="../common/css/main.css" relstylesheet">
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
        <h3 style="margin-top:1rem;">ご質問BOXについて</h3>
        <p>レッスン内容や占星術に関するご質問はこちらからお送りください。</p>

<p>できる限りQ&Aライブでお答えいたします。</p>

<p>ご質問いただく時のコツとしましては・・・<br>
「どうしてその疑問を持ったのか」<br>
「その疑問を解消することで質問者さんがどうなりたいのか」<br>
「どこが不明点なのか」</p>

<p>など、できるだけ具体的に書いていただけると、<br class="pcView">
よりしっかりと疑問を解消するお手伝いができるかと思います。</p>

<p>では、たくさんのご質問をお待ちしております！</p>

<p>アストロクリエイター　星野ワタル</p>

<h3 style="margin-top:3rem;">ご質問フォーム</h3>
          <form method="POST" action="#inquiry" name="inquiry" id="inquiry">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="sec">
              <p><strong>お名前</strong>　<span class="red"><sup>*</sup>必須</span> ハンドルネームでも可能（ライブ配信時にこちらのお名前にてお呼びします）</sup></p>
              <p><input type="text" name="f_handle" value="<?php echo $form_handle_value; ?>" required></p>
            </div>
            <div class="sec">
              <p><strong>お問合せ内容</strong>　<span class="red"><sup>*</sup>必須</span></p>
              <?php if(isset($errMsg) && $errMsg !== ''): ?>
              <p class="red"><?php echo $errMsg; ?></p>
              <?php endif; ?>
              <p><textarea name="f_text" rows="10" required><?php echo $form_text_value; ?></textarea></p>
            </div>
            <div id="btn-sendMail">
              <input type="submit" value="入力内容送信">
            </div>
          </form>
</div>

<h2 style="margin-top:3rem;">会員サイトに関するご質問について</h2>
<div class="Block">
          <p>「メールが届かない」「配信先アドレスを変更したい」<br>
          といった事務的なお問い合わせや「会員サイト」に関するご質問は<br class="pcView">
          <a href="mailto:<?php echo env('MAIL_SUPPORT_ADDRESS', 'info@hoshino-wataru.com'); ?>" style="text-decoration: underline;"><?php echo env('MAIL_SUPPORT_ADDRESS', 'info@hoshino-wataru.com'); ?></a>（THE Imagine事務局）へご連絡ください。</p>

          <p>日曜日を定休として、それ以外の日は24時間以内にお返事いたします。</p>
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
<script src="../common/js/mv-video.js"></script>
<script>
  (function() {
    var form = document.getElementById('inquiry');
    if (!form) return;

    form.addEventListener('submit', function(event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        form.reportValidity();
        return;
      }
      if (!window.confirm('送信してよろしいですか？')) {
        event.preventDefault();
      }
    });
  })();
</script>
</body>
</html>