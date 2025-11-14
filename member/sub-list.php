<?php
  require_once dirname(__FILE__) . '/scripts/env.php';
  require_once dirname(__FILE__) . '/scripts/Session.class.php';
require_once dirname(__FILE__) . '/scripts/model/MemberModel.class.php';
require_once dirname(__FILE__) . '/scripts/model/CategoryModel.class.php';
require_once dirname(__FILE__) . '/scripts/model/SubModel.class.php';
require_once dirname(__FILE__) . '/scripts/model/ContentModel.class.php';
  loadEnv();
  initializeErrorHandling();
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('member') === false) {
    header("Location: login.php");
    exit;
  }

  // カテゴリー取得
  $category_model = new CategoryModel();

  $member_id = $session->get('member');
  $member_model = new MemberModel();
  $member_info = $member_model->select(array('member_id' => $member_id));
  if (empty($member_info)) {
    header("Location: login.php");
    exit;
  }
  $member_info = $member_info[0];
  $course_filter = $member_model->getCourseFilter($member_info['select_course']);

  // サイドバー（レッスン一覧）表示用
  $category_list = (array)$category_model->select(array('indicate_flag'=>1), array('category_number'=>$category_model::ORDER_ASC));
  $category_list = array_values(array_filter($category_list, function($category) {
    $pub_date = isset($category['pub_date']) ? $category['pub_date'] : null;
    return isPublishableNow($pub_date);
  }));

  $number_list = array();
  $title_list = array();
  foreach ($category_list as $ctg) {
    $number_list[$ctg['category_id']] = $ctg['category_number'];
    $title_list[$ctg['category_id']] = $ctg['category_title'];
  }

  // コンテンツ取得
  $sub_model = new SubModel();
  $subs = $sub_model->select(array(
    'indicate_flag'=>$sub_model::ACTIVE,
  ), array('display_order'=>$sub_model::ORDER_ASC));

  // コース別に仕分け
  $advance_subs = array();
  $basic_subs = array();
  foreach ($subs as $sub) {
    $sub_pub_date = isset($sub['pub_date']) ? $sub['pub_date'] : null;
    if (!isPublishableNow($sub_pub_date)) {
      continue;
    }
    $target_course = isset($sub['target_course']) ? $sub['target_course'] : ContentModel::TARGET_COURSE_ADVANCE;
    if ($target_course === ContentModel::TARGET_COURSE_BASIC) {
      if ($course_filter === ContentModel::TARGET_COURSE_BASIC || $course_filter === ContentModel::TARGET_COURSE_ADVANCE) {
        $basic_subs[] = $sub;
      }
    } else {
      if ($course_filter !== ContentModel::TARGET_COURSE_BASIC) {
        $advance_subs[] = $sub;
      }
    }
  }

?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" >
<title>ダウンロード資料一覧 - THE Imagine Members SITE</title>
<meta name="robots" content="noindex,nofollow" />
<meta name="viewport" content="width=device-width">
<link rel="apple-touch-icon" href="/common/img/apple-touch-icon.png">
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
            <img src="/common/img/catBnr-special.png" width="712" height="251" alt=""/>
          </p>
          <div class="txt">
            <p>本講座でお届けした資料やその他特典コンテンツをダウンロードすることができます</p>
          </div>
        </div>
      </section>
      <section id="Quests">
        <?php
          $sub_sections = array();
          if (!empty($advance_subs)) {
            $sub_sections[] = array('title' => 'Advance', 'items' => $advance_subs);
          }
          if (!empty($basic_subs)) {
            $sub_sections[] = array('title' => 'Basic', 'items' => $basic_subs);
          }
        ?>
        <?php if(!empty($sub_sections)): ?>
          <?php foreach($sub_sections as $section): ?>
            <h2 style="margin-bottom: 10px;"><?php echo $section['title']; ?></h2>
            <ul class="flexList">
              <?php foreach($section['items'] as $s) : ?>
              <li class="Hv">
                <a href="<?php echo $s['content_url'];?>" target="_blank">
                  <div class="thumb">
                    <?php
                      $thumbnail = $s['thumbnail_url'];
                      $piThumb = is_string($thumbnail) ? pathinfo($thumbnail) : array();
                      $dirThumb = (isset($piThumb['dirname']) && $piThumb['dirname'] !== '.' && $piThumb['dirname'] !== '') ? $piThumb['dirname'].'/' : '';
                      $nameThumb = isset($piThumb['filename']) ? $piThumb['filename'] : (isset($piThumb['basename']) ? preg_replace('/\.[^.]*$/','',$piThumb['basename']) : '');
                      $baseThumb = $dirThumb . $nameThumb;
                      $jpg640 = $baseThumb !== '' ? $baseThumb . '_640.jpg' : '';
                      $jpg1280 = $baseThumb !== '' ? $baseThumb . '_1280.jpg' : '';
                      $webp640 = $baseThumb !== '' ? $baseThumb . '_640.webp' : '';
                      $webp1280 = $baseThumb !== '' ? $baseThumb . '_1280.webp' : '';
                      $rootDir = dirname(__FILE__);
                      $exists_webp640 = ($webp640 !== '') && file_exists($rootDir . '/' . ltrim($webp640, '/'));
                      $exists_webp1280 = ($webp1280 !== '') && file_exists($rootDir . '/' . ltrim($webp1280, '/'));
                      $exists_jpg640 = ($jpg640 !== '') && file_exists($rootDir . '/' . ltrim($jpg640, '/'));
                      $exists_jpg1280 = ($jpg1280 !== '') && file_exists($rootDir . '/' . ltrim($jpg1280, '/'));
                      $fallbackThumb = !empty($thumbnail) ? $thumbnail : 'common/img/no_image.png';
                    ?>
                    <picture>
                      <?php if ($exists_webp640 || $exists_webp1280): ?>
                      <source type="image/webp" srcset="<?php echo $exists_webp640 ? htmlspecialchars($webp640, ENT_QUOTES, 'UTF-8').' 640w' : ''; ?><?php echo ($exists_webp640 && $exists_webp1280) ? ', ' : ''; ?><?php echo $exists_webp1280 ? htmlspecialchars($webp1280, ENT_QUOTES, 'UTF-8').' 1280w' : ''; ?>" sizes="(max-width: 768px) 320px, 640px">
                      <?php endif; ?>
                      <?php if ($exists_jpg640 || $exists_jpg1280): ?>
                      <source type="image/jpeg" srcset="<?php echo $exists_jpg640 ? htmlspecialchars($jpg640, ENT_QUOTES, 'UTF-8').' 640w' : ''; ?><?php echo ($exists_jpg640 && $exists_jpg1280) ? ', ' : ''; ?><?php echo $exists_jpg1280 ? htmlspecialchars($jpg1280, ENT_QUOTES, 'UTF-8').' 1280w' : ''; ?>" sizes="(max-width: 768px) 320px, 640px">
                      <?php endif; ?>
                      <img src="<?php echo htmlspecialchars($fallbackThumb, ENT_QUOTES, 'UTF-8'); ?>" width="217" height="150" loading="lazy" decoding="async" alt=""/>
                    </picture>
                  </div>
                  <div class="txt">
                    <div class="category-label">
                      <?php
                        $category_title = isset($title_list[$s['category_id']]) ? $title_list[$s['category_id']] : 'THE Imagine';
                        echo htmlspecialchars($category_title, ENT_QUOTES, 'UTF-8');
                      ?>
                    </div>
                    <div class="title"><?php echo htmlspecialchars($s['content_title'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="summery"><?php echo strip_tags($s['content_text']); ?></div>
                  </div>
                </a>
              </li>
              <?php endforeach; ?>
            </ul>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="text-align:center;">現在閲覧可能な資料はありません。</p>
        <?php endif; ?>
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