<?php
  require_once dirname(__FILE__) . '/scripts/Session.class.php';
  require_once dirname(__FILE__) . '/scripts/model/NewsModel.class.php';
  require_once dirname(__FILE__) . '/scripts/model/MemberModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('member') === false) {
    header("Location: login.php");
    exit;
  }

  $member_id = $session->get('member');
  $member_model = new MemberModel();
  $member_info = $member_model->select(array('member_id' => $member_id));
  if (empty($member_info)) {
    header("Location: login.php");
    exit;
  }
  $member_info = $member_info[0];
  $course_filter = $member_model->getCourseFilter($member_info['select_course']);

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
    if (!$news_model->isVisibleForCourse($news, $course_filter)) {
      header("Location: index.php");
      exit;
    }
  }

?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" >
<title>ニュース詳細 - THE Imagine Members SITE</title>
<meta name="robots" content="noindex,nofollow" />
<meta name="viewport" content="width=device-width">
<link rel="apple-touch-icon" href="/common/img/apple-touch-icon.png">
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
            <?php
              $news_text = $news['text'];
              $pattern = '/(https?:\/\/[^\s<>"\']+)/i';
              $converted_text = preg_replace_callback($pattern, function($matches) {
                $url = $matches[1];
                $escaped_url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
                return '<a href="' . $escaped_url . '" target="_blank" rel="noopener">' . $escaped_url . '</a>';
              }, $news_text);
              $email_pattern = '/([a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,})/i';
              $converted_text = preg_replace_callback($email_pattern, function($matches) {
                $email = $matches[1];
                $escaped_email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
                return '<a href="mailto:' . $escaped_email . '">' . $escaped_email . '</a>';
              }, $converted_text);
              echo nl2br($converted_text);
            ?>
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