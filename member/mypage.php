<?php
  require_once dirname(__FILE__) . '/scripts/Session.class.php';
  require_once dirname(__FILE__) . '/scripts/model/MemberModel.class.php';
  require_once dirname(__FILE__) . '/scripts/validate.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('member') === false) {
    header("Location: login.php");
    exit;
  }

  // メンバー情報取得
  $member_model = new MemberModel($member_id = NULL);
  $member_list = $member_model->select(array('member_id'=>$session->get('member')));
  $member = $member_list[0];
  $mail = $member['login_mail'];
  $password = $member['login_password'];
  $course_value = isset($member['select_course']) ? (int)$member['select_course'] : null;
  $course_label = $member_model->getMemberCourseName($course_value);

  if($_SERVER["REQUEST_METHOD"] == "POST") {
    // バリデーション用にデータを加工
    $validate_data = array();
    $opt_mail = array(
      'is_required' => true,
      'type' => 'mail'
    );
    $opt_passwd = array(
      'is_required' => true,
      'type' => 'password'
    );
    $validate_data[] = array('mail', $_POST['mail'], $opt_mail);
    $validate_data[] = array('password', $_POST['password'], $opt_passwd);

    $errList = validate($validate_data);
    $errFlg = false;
    foreach ($errList as $v) {
      if(!empty($v)) {
        $errFlg = true;
      }
    }

    // バリデーションエラーがなければ登録
    if(!$errFlg) {
      $success = $member_model->update(array(
        'login_mail' => $_POST['mail'],
        'login_password' => $_POST['password'],
      ), array('member_id'=>$_POST['mid']));

      if($success) {
        header("Location: edit-complete.php");
        exit;
      } else {
        header("Location: index.php");
        exit;
      }
    }
  }

?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" >
<title>登録情報の確認 - THE Imagine Membersサイト</title>
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
        <h2>登録情報の確認</h2>
        <div class="Block">
          <p class="midNote">登録されているログインID（メールアドレス）/パスワードはこちらになります。</p>
          <div class="MyAccount">
            <input type="hidden" name="mid" value="<?php echo $session->get('member'); ?>">
            <dl>
              <dt><strong>ログインID（メールアドレス）</strong></dt>
              <dd><?php echo $mail; ?></dd>
            </dl>
            <dl>
              <dt><strong>パスワード</strong></dt>
              <dd><?php echo $password; ?></dd>
            </dl>
            <dl>
              <dt><strong>対象コース</strong></dt>
              <dd><?php echo htmlspecialchars($course_label, ENT_QUOTES, 'UTF-8'); ?></dd>
            </dl>
          </div>
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