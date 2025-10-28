<?php
  require_once dirname(__FILE__) . '/scripts/Session.class.php';
  require_once dirname(__FILE__) . '/scripts/model/MemberModel.class.php';
  require_once dirname(__FILE__) . '/scripts/model/CategoryModel.class.php';
  require_once dirname(__FILE__) . '/scripts/model/ContentModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('member') === false) {
    header("Location: login.php");
    exit;
  }

  // カテゴリー取得
  $category_model = new CategoryModel();
  $category = $category_model->select(array(
    'category_id'=>$_GET['ctg_id'],
    'indicate_flag'=>$category_model::ACTIVE,
  ));
  if(empty($category)) {
    header("Location: index.php");
    exit;
  } else {
    $category = $category[0];
  }

  // サイドバー（レッスン一覧）表示用
  $category_list = $category_model->select(array('indicate_flag'=>1), array('category_number'=>$category_model::ORDER_ASC));

  // コンテンツ取得
  $content_model = new ContentModel();
  $contents = $content_model->select(array(
    'category_id'=>$category['category_id'],
    'indicate_flag'=>$content_model::ACTIVE,
  ), array('display_order'=>$content_model::ORDER_ASC));

?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" >
<title>コンテンツ一覧 - THE Imagine Members SITE</title>
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
      <?php if ($category['category_number'] == '12'): ?>
        <h2>イマジンラジオ</h2>
      <?php elseif ($category['category_number'] == '11'): ?>
        <h2>QAライブ動画</h2>
      <?php else: ?>
        <h2>Lesson<?php echo $category['category_number'];?>：<?php echo $category['category_title'];?></h2>
      <?php endif; ?>
        <div class="Block">
          <p class="Photo">
            <img src="<?php echo $category['category_list_img'];?>" width="712" height="251" alt=""/>
          </p>
          <div class="txt">
            <p><?php echo nl2br($category['content_text']);?></p>
          </div>
        </div>
      </section>
      <section id="Quests">
        <ul>
          <?php foreach($contents as $c) : ?>
          <li class="Hv">
            <a href="detail.php?cont_id=<?php echo $c['content_id'];?>">
              <div class="thumb">
                <img src="<?php echo $c['thumbnail_url']; ?>" width="217" height="150" alt=""/>
              </div>
              <div class="txt">
                <div class="number">Week <?php echo $c['content_week']; ?></div>
                <div class="title"><?php echo htmlspecialchars($c['content_title'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="summery"><?php echo strip_tags($c['content_text']); ?></div>
              </div>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </section>
    </div>
    <!-- /Main -->

    <div id="Side">
      <!-- Side -->
      <div class="Block">
        <?php if ($category['category_number'] == '12'): ?>
        <h3>イマジンラジオの達成度</h3>
      <?php elseif ($category['category_number'] == '11'): ?>
        <h3>QAライブ動画の達成度</h3>
      <?php else: ?>
        <h3>Lesson<?php echo $category['category_number']; ?>の達成度</h3>
      <?php endif; ?>
        <div class="cnt">
          <div class="col-lg">
            <div id="circle"></div>
          </div>
        </div>
      </div>

      <?php include 'sidebar.php'; ?>
      <div class="Block">
        <h3>レッスン一覧</h3>
        <div class="cntQuest">
          <ul>
            <?php foreach ($category_list as $c): ?>
            <a href="list.php?ctg_id=<?php echo $c['category_id']; ?>">
              <li>
                <?php if ($c['category_number'] == '12'): ?>
                <p class="Month">イマジンラジオ</p>
              <?php elseif ($c['category_number'] == '11'): ?>
                <p class="Month">QAライブ動画</p>
              <?php else: ?>
                <p class="Month">Lesson<?php echo $c['category_number']; ?></p>
              <?php endif; ?>
                <p class="Title"><?php echo $c['category_title']; ?></p>
              </li>
            </a>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
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