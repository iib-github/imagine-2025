<?php
  require_once dirname(__FILE__) . '/scripts/Session.class.php';
  require_once dirname(__FILE__) . '/scripts/model/MemberModel.class.php';
  require_once dirname(__FILE__) . '/scripts/model/CategoryModel.class.php';
  require_once dirname(__FILE__) . '/scripts/model/SubModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('member') === false) {
    header("Location: login.php");
    exit;
  }

  // カテゴリー取得
  $category_model = new CategoryModel();

  // サイドバー（レッスン一覧）表示用
  $category_list = $category_model->select(array('indicate_flag'=>1), array('category_number'=>$category_model::ORDER_ASC));

  $number_list = array();
  foreach ($category_list as $ctg) {
    $number_list[$ctg['category_id']] = $ctg['category_number'];
  }

  // コンテンツ取得
  $sub_model = new SubModel();
  $subs = $sub_model->select(array(
    // 'category_id'=>$category['category_id'],
    'indicate_flag'=>$sub_model::ACTIVE,
  ), array('display_order'=>$sub_model::ORDER_ASC));

?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" >
<title>ダウンロード資料一覧 - THE Imagine Members SITE</title>
<meta name="robots" content="noindex,nofollow" />
<meta name="viewport" content="width=device-width">
<link rel="apple-touch-icon" href="/membership/member/common/img/apple-touch-icon.png">
<link href="common/css/main.css?date=20170614220000" rel="stylesheet">
<link href="common/css/jquery.circliful.css" rel="stylesheet" type="text/css" />
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="common/js/jquery.circliful.min.js"></script>
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<script src="common/js/respond.min.js"></script>
<![endif]-->
<!--[if IE 6]><script src="common/js/minmax.js"></script><![endif]-->
<?php include 'tmp/analytics.php';?>
</head>

<body>
<?php include 'tmp/header.php';?>
<div id="wrapper">
  <!-- Wrapper -->
  <div id="ContentsMrg">
    <!-- Contents -->
    <div id="Main">
      <!-- Main -->
      <section id="ContentsDetail">
        <h2>ダウンロード資料一覧</h2>
        <div class="Block">
          <p class="Photo">
            <img src="/membership/member/common/img/catBnr-special.png" width="712" height="251" alt=""/>
          </p>
          <div class="txt">
            <p>本講座でお届けした資料やその他特典コンテンツをダウンロードすることができます</p>
          </div>
        </div>
      </section>
      <section id="Quests">
        <ul class="flexList">
          <?php foreach($subs as $s) : ?>
          <li class="Hv">
            <a href="<?php echo $s['content_url'];?>" target="_blank">
              <div class="thumb">
<?php if (!empty($s['thumbnail_url'])): ?>
                <img src="<?php echo $s['thumbnail_url']; ?>" width="217" height="150" alt=""/>
<?php else: ?>
                <img src="contents/content/cont_86-thumbnail.png" width="217" height="150" alt=""/>
<?php endif ?>
              </div>
              <div class="txt">
                <div class="number">Lesson<?php echo $number_list[$s['category_id']]; ?></div>
                <div class="title"><?php echo htmlspecialchars($s['content_title'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="summery"><?php echo strip_tags($s['content_text']); ?></div>
              </div>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </section>
    </div>
    <!-- /Main -->

    <div id="Side">
      <?php include 'sidebar.php'; ?>
    </div>
    <!-- /Side -->
  </div>
  <!-- /Contents -->
<?php include 'tmp/footer.php';?>
</div>
<!-- /Wrapper -->

<script>
$(".summery").each(function(){
  var size = 130;
  var txt = $(this).text();
  var suffix = '…';
  var b = 0;
  for(var i = 0; i < txt.length; i++) {
    b += txt.charCodeAt(i) <= 255 ? 0.5 : 1;
    if (b > size) {
      txt = txt.substr(0, i) + suffix;
      break;
    }
  }
  $(this).text(txt);
});
</script>
<script>
<?php
  $member_model = new MemberModel();
  $score = $member_model->getScore($session->get('member'), $category['category_id']);
?>
  $(document).ready(function() { // 6,32 5,38 2,34
      $("#circle").circliful({
          animation: 1,
          animationStep: 5,
          foregroundBorderWidth: 15,
          backgroundBorderWidth: 15,
          percent: <?php echo $score; ?>,
          textSize: 28,
          textStyle: 'font-size: 12px;',
          textColor: '#666',
          multiPercentage: 1,
          percentages: [10, 20, 30]
      });
  });
</script>
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