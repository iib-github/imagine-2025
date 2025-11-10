<?php
  require_once dirname(__FILE__) . '/scripts/env.php';
  require_once dirname(__FILE__) . '/scripts/Session.class.php';
  require_once dirname(__FILE__) . '/scripts/model/MemberModel.class.php';
  require_once dirname(__FILE__) . '/scripts/model/CategoryModel.class.php';
  require_once dirname(__FILE__) . '/scripts/model/ContentModel.class.php';
require_once dirname(__FILE__) . '/scripts/model/MemberContentRelation.class.php';
  
  // .envファイルを読み込み、エラーハンドリングを初期化
  loadEnv();
  initializeErrorHandling();
  
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

  $category_title = !empty($category['category_title'])
    ? $category['category_title']
    : 'Lesson' . $category['category_number'];
  $category_use_week = !isset($category['use_week_flag']) || (int)$category['use_week_flag'] === 1;
  $category_description_raw = isset($category['content_text']) ? trim($category['content_text']) : '';
  $has_category_description = (mb_strlen(strip_tags($category_description_raw)) > 0);
  $photo_margin_class = $has_category_description ? '' : ' category_photo--compact';

  // アクセス制限: ベーシックユーザーがアドバンス専用カテゴリを閲覧できないようにする
  $category_target_course = isset($category['target_course']) ? $category['target_course'] : ContentModel::TARGET_COURSE_ADVANCE;
  if ($course_filter === ContentModel::TARGET_COURSE_BASIC && $category_target_course === ContentModel::TARGET_COURSE_ADVANCE) {
    header("Location: index.php");
    exit;
  }

  // サイドバー（レッスン一覧）表示用
  $all_categories = $category_model->select(array('indicate_flag'=>1), array('category_number'=>$category_model::ORDER_ASC));
  $category_list = $category_model->filterCategoriesByCourse($all_categories, $course_filter);

  // コンテンツ取得
  $content_model = new ContentModel();
  $relation_model = new MemberContentRelation();
  
  $where_conditions = array(
    'category_id'=>$category['category_id'],
    'indicate_flag'=>$content_model::ACTIVE,
  );
  
  // コースフィルタを適用
  $contents = $content_model->select($where_conditions, array('display_order'=>$content_model::ORDER_ASC));
  
  // 会員のコースに基づいてフィルタリング
  $filtered_contents = array();
  foreach ($contents as $content) {
    // target_courseがNULLの場合は常に表示
    if (empty($content['target_course']) || $content['target_course'] === ContentModel::TARGET_COURSE_ADVANCE) {
      $filtered_contents[] = $content;
    } elseif ($course_filter === ContentModel::TARGET_COURSE_ADVANCE) {
      // アドバンス会員は全コンテンツ表示
      $filtered_contents[] = $content;
    } elseif ($content['target_course'] === $course_filter) {
      // ベーシック会員はベーシックコンテンツのみ表示
      $filtered_contents[] = $content;
    }
  }
  $contents = $filtered_contents;

$completed_content_map = array();
$completed_records = $relation_model->select(array(
  'member_id' => $member_id,
  'category_id' => $category['category_id'],
));
foreach ($completed_records as $record) {
  if (isset($record['content_id'])) {
    $completed_content_map[(int)$record['content_id']] = true;
  }
}

?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" >
<title>コンテンツ一覧 - THE Imagine Members SITE</title>
<meta name="robots" content="noindex,nofollow" />
<meta name="viewport" content="width=device-width">
<link rel="apple-touch-icon" href="/common/img/apple-touch-icon.png">
<link href="common/css/main.css?date=20170614220000" rel="stylesheet">
<link href="common/css/jquery.circliful.css" rel="stylesheet" type="text/css" />
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="common/js/jquery.circliful.min.js"></script>
<style>
  .status-label{
    display:inline-block;
    padding:2px 12px;
    border-radius:14px;
    font-size:12px;
    font-weight:bold;
    color:#fff;
  }
  .label-row{
    display:flex;
    align-items:center;
    gap:8px;
    margin-bottom:6px;
  }
  .status-label--complete{background:#4CAF50;}
  .status-label--incomplete{background:#9e9e9e;}
  .category_description--empty {
    margin: 0 !important;
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
<?php include 'tmp/header.php';?>
<div id="wrapper">
  <!-- Wrapper -->
  <div id="ContentsMrg">
    <!-- Contents -->
    <div id="Main">
      <!-- Main -->
      <section id="ContentsDetail">
        <h2><?php echo htmlspecialchars($category_title, ENT_QUOTES, 'UTF-8'); ?></h2>
        <div class="Block">
          <p class="Photo<?php echo $photo_margin_class; ?>">
            <?php
              $hdr = $category['category_list_img'];
              $piH = pathinfo($hdr);
              $baseH = $piH['dirname'] . '/' . (isset($piH['filename']) ? $piH['filename'] : '');
              $jpg640H = $baseH . '_640.jpg';
              $jpg1280H = $baseH . '_1280.jpg';
              $webp640H = $baseH . '_640.webp';
              $webp1280H = $baseH . '_1280.webp';
              $rootDir = dirname(__FILE__);
              $exists_webp640H = file_exists($rootDir . '/' . ltrim($webp640H, '/'));
              $exists_webp1280H = file_exists($rootDir . '/' . ltrim($webp1280H, '/'));
              $exists_jpg640H = file_exists($rootDir . '/' . ltrim($jpg640H, '/'));
              $exists_jpg1280H = file_exists($rootDir . '/' . ltrim($jpg1280H, '/'));
            ?>
            <picture>
              <?php if ($exists_webp640H || $exists_webp1280H): ?>
              <source type="image/webp" srcset="<?php echo $exists_webp640H ? htmlspecialchars($webp640H, ENT_QUOTES, 'UTF-8').' 640w' : ''; ?><?php echo ($exists_webp640H && $exists_webp1280H) ? ', ' : ''; ?><?php echo $exists_webp1280H ? htmlspecialchars($webp1280H, ENT_QUOTES, 'UTF-8').' 1280w' : ''; ?>" sizes="(max-width: 768px) 640px, 1280px">
              <?php endif; ?>
              <?php if ($exists_jpg640H || $exists_jpg1280H): ?>
              <source type="image/jpeg" srcset="<?php echo $exists_jpg640H ? htmlspecialchars($jpg640H, ENT_QUOTES, 'UTF-8').' 640w' : ''; ?><?php echo ($exists_jpg640H && $exists_jpg1280H) ? ', ' : ''; ?><?php echo $exists_jpg1280H ? htmlspecialchars($jpg1280H, ENT_QUOTES, 'UTF-8').' 1280w' : ''; ?>" sizes="(max-width: 768px) 640px, 1280px">
              <?php endif; ?>
              <img src="<?php echo htmlspecialchars($hdr, ENT_QUOTES, 'UTF-8'); ?>" width="712" height="251" loading="lazy" decoding="async" alt=""/>
            </picture>
          </p>
          <div class="txt<?php echo $has_category_description ? '' : ' category_description--empty'; ?>">
            <?php if ($has_category_description): ?>
            <p><?php echo nl2br($category_description_raw);?></p>
            <?php endif; ?>
          </div>
        </div>
      </section>
      <section id="Quests">
        <ul>
          <?php foreach($contents as $c) : ?>
          <li class="Hv">
            <a href="detail.php?cont_id=<?php echo $c['content_id'];?>">
              <div class="thumb">
                <?php
                  $tn = $c['thumbnail_url'];
                  $piT = is_string($tn) ? pathinfo($tn) : array();
                  $dirT = (isset($piT['dirname']) && $piT['dirname'] !== '.' && $piT['dirname'] !== '') ? $piT['dirname'].'/' : '';
                  $nameT = isset($piT['filename']) ? $piT['filename'] : (isset($piT['basename']) ? preg_replace('/\.[^.]*$/','',$piT['basename']) : '');
                  $baseT = $dirT . $nameT;
                  $jpg640T = $baseT !== '' ? $baseT . '_640.jpg' : '';
                  $jpg1280T = $baseT !== '' ? $baseT . '_1280.jpg' : '';
                  $webp640T = $baseT !== '' ? $baseT . '_640.webp' : '';
                  $webp1280T = $baseT !== '' ? $baseT . '_1280.webp' : '';
                  $rootDir = dirname(__FILE__);
                  $exists_webp640T = ($webp640T !== '') && file_exists($rootDir . '/' . ltrim($webp640T, '/'));
                  $exists_webp1280T = ($webp1280T !== '') && file_exists($rootDir . '/' . ltrim($webp1280T, '/'));
                  $exists_jpg640T = ($jpg640T !== '') && file_exists($rootDir . '/' . ltrim($jpg640T, '/'));
                  $exists_jpg1280T = ($jpg1280T !== '') && file_exists($rootDir . '/' . ltrim($jpg1280T, '/'));
                ?>
                <picture>
                  <?php if ($exists_webp640T || $exists_webp1280T): ?>
                  <source type="image/webp" srcset="<?php echo $exists_webp640T ? htmlspecialchars($webp640T, ENT_QUOTES, 'UTF-8').' 640w' : ''; ?><?php echo ($exists_webp640T && $exists_webp1280T) ? ', ' : ''; ?><?php echo $exists_webp1280T ? htmlspecialchars($webp1280T, ENT_QUOTES, 'UTF-8').' 1280w' : ''; ?>" sizes="(max-width: 768px) 320px, 640px">
                  <?php endif; ?>
                  <?php if ($exists_jpg640T || $exists_jpg1280T): ?>
                  <source type="image/jpeg" srcset="<?php echo $exists_jpg640T ? htmlspecialchars($jpg640T, ENT_QUOTES, 'UTF-8').' 640w' : ''; ?><?php echo ($exists_jpg640T && $exists_jpg1280T) ? ', ' : ''; ?><?php echo $exists_jpg1280T ? htmlspecialchars($jpg1280T, ENT_QUOTES, 'UTF-8').' 1280w' : ''; ?>" sizes="(max-width: 768px) 320px, 640px">
                  <?php endif; ?>
                  <img src="<?php echo htmlspecialchars($tn, ENT_QUOTES, 'UTF-8'); ?>" width="217" height="150" loading="lazy" decoding="async" alt=""/>
                </picture>
              </div>
              <div class="txt">
                <div class="label-row">
                  <?php
                    $is_completed = isset($completed_content_map[(int)$c['content_id']]);
                    $status_label = $is_completed ? '視聴済み' : '未視聴';
                    $status_class = $is_completed ? 'status-label status-label--complete' : 'status-label status-label--incomplete';
                  ?>
                  <div class="<?php echo $status_class; ?>"><?php echo $status_label; ?></div>
                </div>
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
      <h3><?php echo htmlspecialchars($category_title, ENT_QUOTES, 'UTF-8'); ?>の達成度</h3>
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
              <?php
                $c_title = !empty($c['category_title'])
                  ? $c['category_title']
                  : 'Lesson' . $c['category_number'];
                $course_label = 'アドバンス';
                if (isset($c['target_course'])) {
                  switch ($c['target_course']) {
                    case ContentModel::TARGET_COURSE_BASIC:
                    case 'basic':
                      $course_label = 'ベーシック';
                      break;
                    case ContentModel::TARGET_COURSE_ADVANCE:
                    case 'advance':
                      $course_label = 'アドバンス';
                      break;
                    default:
                      $course_label = '全体';
                      break;
                  }
                }
              ?>
                <p class="Month"><?php echo htmlspecialchars($course_label, ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="Title"><?php echo htmlspecialchars($c['category_title'], ENT_QUOTES, 'UTF-8'); ?></p>
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