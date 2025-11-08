<?php
  require_once dirname(__FILE__) . '/scripts/Session.class.php';
  require_once dirname(__FILE__) . '/scripts/model/MemberModel.class.php';
  require_once dirname(__FILE__) . '/scripts/env.php';
  $session = Session::getInstance();

  loadEnv();

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
      $session->set('show_login_splash', true);
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
    <p class="ForgotPwTrigger">
      <button type="button" id="openForgotPw" class="ForgotPwTrigger__btn">パスワードをお忘れの方はこちら</button>
    </p>
  </div>
</section>

<div class="ForgotPwModal" id="forgotPwModal" aria-hidden="true">
  <div class="ForgotPwModal__overlay" data-forgot-close></div>
  <div class="ForgotPwModal__content" role="dialog" aria-modal="true" aria-labelledby="forgotPwTitle">
    <button type="button" class="ForgotPwModal__close" data-forgot-close aria-label="閉じる">×</button>
    <h2 id="forgotPwTitle">パスワード再送のご案内</h2>
    <p class="ForgotPwModal__lead">登録済みのメールアドレスを入力すると、パスワードを記載したメールをお送りします。</p>
    <form id="forgotPwForm">
      <label class="ForgotPwModal__label">
        <span>メールアドレス</span>
        <input type="email" name="login_mail" required placeholder="example@example.com" autocomplete="email">
      </label>
      <p class="ForgotPwModal__message" id="forgotPwMessage"></p>
      <div class="ForgotPwModal__actions">
        <button type="submit" class="ForgotPwModal__submit">送信する</button>
      </div>
    </form>
  </div>
</div>

<script type="text/javascript">
// Enterボタンでログイン
$(".UserInput, .PassInput").keypress(function(e) {
  if(e.which == 13) {
    $("#frm").submit();
  }
});

</script>
<script>
  (function(){
    var modal = document.getElementById('forgotPwModal');
    var openBtn = document.getElementById('openForgotPw');
    if(!modal || !openBtn) return;

    var form = document.getElementById('forgotPwForm');
    var messageEl = document.getElementById('forgotPwMessage');

    function openModal(){
      modal.classList.add('is-active');
      modal.setAttribute('aria-hidden', 'false');
      if(messageEl){
        messageEl.textContent = '';
        messageEl.className = 'ForgotPwModal__message';
      }
      if(form){
        form.reset();
      }
      var emailInput = form ? form.querySelector('input[name="login_mail"]') : null;
      if(emailInput){
        setTimeout(function(){ emailInput.focus(); }, 50);
      }
    }
    function closeModal(){
      modal.classList.remove('is-active');
      modal.setAttribute('aria-hidden', 'true');
    }

    openBtn.addEventListener('click', openModal);
    modal.addEventListener('click', function(e){
      if(e.target && e.target.hasAttribute('data-forgot-close')){
        closeModal();
      }
    });
    var closeBtn = modal.querySelector('.ForgotPwModal__close');
    if(closeBtn){
      closeBtn.addEventListener('click', closeModal);
    }
    document.addEventListener('keydown', function(e){
      if(e.key === 'Escape' && modal.classList.contains('is-active')){
        closeModal();
      }
    });
    if(form){
      form.addEventListener('submit', function(e){
        e.preventDefault();
        if(!messageEl) return;
        var formData = new FormData(form);
        var email = (formData.get('login_mail') || '').trim();
        if(!email){
          messageEl.textContent = 'メールアドレスを入力してください。';
          messageEl.classList.add('is-error');
          return;
        }
        messageEl.textContent = '送信しています…';
        messageEl.classList.remove('is-error', 'is-success');

        fetch('forgot-password.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
          },
          body: new URLSearchParams(formData).toString()
        })
        .then(function(res){ return res.json(); })
        .then(function(json){
          if(json && json.success){
            messageEl.textContent = '入力いただいたメールアドレス宛にパスワード再送メールを送信しました。ご確認ください。';
            messageEl.classList.add('is-success');
          } else {
            messageEl.textContent = json && json.message ? json.message : '送信に失敗しました。時間をおいて再度お試しください。';
            messageEl.classList.add('is-error');
          }
        })
        .catch(function(){
          messageEl.textContent = '送信に失敗しました。時間をおいて再度お試しください。';
          messageEl.classList.add('is-error');
        });
      });
    }
  })();
</script>
</body>
</html>