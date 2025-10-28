<?php
  require_once dirname(__FILE__) . '/scripts/Session.class.php';
  require_once dirname(__FILE__) . '/scripts/model/NewsModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('member') === false) {
    header("Location: login.php");
    exit;
  }

  // コンテンツ取得
  $news_model = new NewsModel();
  $news = $news_model->select(array(
    'id'=>$_GET['id'],
    'is_active'=>$news_model::ACTIVE,
  ));
  if(empty($news)) {
    header("Location: index.php");
    exit;
  } else {
    $news = $news[0];
  }

?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" >
<title>ニュース詳細 - THE Imagine Members SITE</title>
<meta name="robots" content="noindex,nofollow" />
<meta name="viewport" content="width=device-width">
<link rel="apple-touch-icon" href="/membership/member/common/img/apple-touch-icon.png">
<link href="common/css/main.css?date=20170614220000" rel="stylesheet">
<link href="common/css/jquery.circliful.css" rel="stylesheet" type="text/css" />
<link href="https://fonts.googleapis.com/css?family=Questrial" rel="stylesheet">

<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="common/js/jquery.circliful.min.js"></script>
<script src="common/js/complete.js?=<?php echo time(); ?>"></script>
<!--[if lt IE 9]>
<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
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
  <div id="ContentsMrg">
    <!-- Contents -->
    <div id="Main">
      <!-- Main -->
      <section id="ContentsDetail">
        <h2><?php echo $news['description']; ?></h2>
        <div class="Block">
          <div class="txtBlock ta-r">
            <?php echo mb_substr($news['note_date'], 0, 10); ?>
          </div>
          <div class="txtBlock">
            <?php echo nl2br($news['text']);?>
          </div>
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

<script>
  $(function(){
      $("#menuButton").click(function(){
      $(this).toggleClass("active"); //メニューボタンの切り替え

      /*-- メニューの開閉 --*/
      if($(this).hasClass("active")){
        $("body").css("oveflow", "hidden");
        $("main").animate({
          "left": "-50%"
        }, 400);
        $("aside")
          .show()
          .animate({
            "left": "50%"
          }, 400);
      }else{
        $("main").animate({
          "left": 0
        }, 400);
        $("aside")
          .show()
          .animate({
            "left": "100%"
          }, 400, function(){
            $("aside").hide();
            $("body").css("oveflow", "visuble");
          });
      }

          return false;
      });
  });
</script>
<script src="common/js/smoothscroll.js"></script>
</body>
</html>