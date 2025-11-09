<?php
  require_once dirname(__FILE__) . '/scripts/env.php';
  require_once dirname(__FILE__) . '/scripts/Session.class.php';
  require_once dirname(__FILE__) . '/scripts/model/CategoryModel.class.php';
  require_once dirname(__FILE__) . '/scripts/model/NewsModel.class.php';
  require_once dirname(__FILE__) . '/scripts/model/MemberModel.class.php';
  require_once dirname(__FILE__) . '/scripts/model/ContentModel.class.php';
  
  // .envファイルを読み込み、エラーハンドリングを初期化
  loadEnv();
  initializeErrorHandling();
  
  $session = Session::getInstance();

  // お知らせの取得数
  const NEWS_NUM = 5;

  // セッションがなければログイン画面に遷移させる。
  if($session->get('member') === false) {
    header("Location: login.php");
    exit;
  }
  
  $show_login_splash = $session->get('show_login_splash');
  if($show_login_splash) {
    $session->clear('show_login_splash');
  }

  $member_id = $session->get('member');
  // 会員モデルとコンテンツモデルを初期化
  $member_model = new MemberModel();
  $content_model = new ContentModel();
  
// テーマ切り替え（デザイン検証用）
$theme = isset($_GET['theme']) ? $_GET['theme'] : null;
$theme_class = '';
if ($theme === 'blue') {
  $theme_class = 'theme-blue';
} elseif ($theme === 'teal') {
  $theme_class = 'theme-teal';
}

  // 会員情報を取得
  $member_info = $member_model->select(array('member_id' => $member_id));
  if (empty($member_info)) {
      // エラーハンドリング、またはログインページへリダイレクト
      header("Location: login.php");
      exit;
  }
  $member_info = $member_info[0];
  $course_filter = $member_model->getCourseFilter($member_info['select_course']);

  if (!function_exists('cached_file_exists')) {
    function cached_file_exists($path) {
      static $cache = array();
      if (array_key_exists($path, $cache)) {
        return $cache[$path];
      }
      $cache[$path] = file_exists($path);
      return $cache[$path];
    }
  }

  // 有効なカテゴリーを全て取得
  $category_model = new CategoryModel();
  $all_categories = $category_model->select(array('indicate_flag'=>1), array('category_number'=>$category_model::ORDER_ASC));
  $category_list = $category_model->filterCategoriesByCourse($all_categories, $course_filter);

  // カテゴリーをコース別に分類
  $advance_categories = array();
  $basic_categories = array();
  foreach ($category_list as $category) {
    $target = isset($category['target_course']) ? $category['target_course'] : ContentModel::TARGET_COURSE_ADVANCE;
    if ($target === ContentModel::TARGET_COURSE_ADVANCE) {
      $advance_categories[] = $category;
    } elseif ($target === ContentModel::TARGET_COURSE_BASIC) {
      $basic_categories[] = $category;
    } else { // undefined -> 双方に表示
      $advance_categories[] = $category;
      $basic_categories[] = $category;
    }
  }

  $is_basic_user = ($course_filter === ContentModel::TARGET_COURSE_BASIC);
  
  // 会員のコース別進捗情報を取得
  $member_progress = $member_model->getMemberCourseProgress($member_id);
  $total_contents = $member_progress['total_contents'];
  $completed_contents = $member_progress['completed_contents'];
  $completion_rate = $member_progress['completion_rate'];

  $category_ids = array();
  foreach ($advance_categories as $category) {
    $category_ids[] = (int)$category['category_id'];
  }
  foreach ($basic_categories as $category) {
    $category_ids[] = (int)$category['category_id'];
  }
  $category_ids = array_values(array_unique(array_filter($category_ids)));
  $category_progress_map = array();
  if (!empty($category_ids)) {
    $category_progress_map = $member_model->getMemberCourseProgressByCategories($member_id, $category_ids);
  }

  // お知らせ取得
  $news_model = new NewsModel();
  $news_list = $news_model->getNewsList(NEWS_NUM, $course_filter);

?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" >
<meta name="robots" content="noindex,nofollow" />
<meta name="viewport" content="width=device-width">
<title>THE Imagine Membersサイト</title>
<link rel="apple-touch-icon" href="/common/img/apple-touch-icon.png">
<link href="common/css/main.css?date=201707132215" rel="stylesheet">
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<script src="common/js/respond.min.js"></script>
<![endif]-->
<!--[if IE 6]><script src="common/js/minmax.js"></script><![endif]-->
<script type="text/javascript">
window.onload = function(){
var url=[];
url[0] = 'bg01.png';
url[1] = 'bg02.png';
url[2] = 'bg03.png';
url[3] = 'bg04.png';
// url[4] = 'bg05.png';
var n = Math.floor(Math.random() * url.length);
var elm = document.getElementById('MV');
elm.style.backgroundImage = 'url(common/img/' + url[n] + ')';
}
</script>
<?php include 'tmp/analytics.php';?>
</head>

<body class="<?php echo htmlspecialchars($theme_class, ENT_QUOTES, 'UTF-8'); ?>">
<?php if($show_login_splash): ?>
<div class="login-splash" id="loginSplash">
  <div class="login-splash__logo-wrapper">
    <img src="common/img/login_logo.png" alt="THE Imagine" class="login-splash__logo" loading="lazy" decoding="async">
  </div>
</div>
<?php endif; ?>
<section id="MV">
  <div class="Cnt">
    <h1><img src="common/img/login_logo.png" width="320" alt="THE Imagine" loading="lazy" decoding="async"/></h1>
  </div>
</section>
<div id="wrapper"><!-- Wrapper -->
  <div id="Contents"><!-- Contents -->
    <div id="Main"><!-- Main -->
      <!-- <section id="Progress">
        <h2>学習進捗</h2>
        <div class="Block">
          <p>現在のコース: <strong><?php echo htmlspecialchars($member_model->getMemberCourseName($member_info['select_course'])); ?></strong></p>
          <p>合計コンテンツ数: <strong class="progress-count"><span><?php echo $total_contents; ?></span></strong></p>
          <p>完了コンテンツ数: <strong class="progress-count"><span><?php echo $completed_contents; ?></span></strong></p>
          <div class="progress-bar-container">
            <div class="progress-bar-fill" style="width: <?php echo $completion_rate; ?>%;"></div>
            <span class="progress-text"><?php echo $completion_rate; ?>% 完了</span>
          </div>
          <p class="progress-detail-link"><a href="progress.php">進捗詳細を見る</a></p>
        </div>
      </section> -->
      <section id="News">
        <h2>更新情報</h2>
        <div class="Block">
          <ul>
            <?php foreach ($news_list as $n): ?>
            <li>
              <a href="news-detail.php?id=<?php echo $n['id']?>">
              <dl>
                <dt><?php echo mb_substr($n['note_date'], 0, 10); ?></dt>
                <dd><?php echo htmlspecialchars($n['description'], ENT_QUOTES, 'UTF-8'); ?></dd>
              </dl>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </section>

      <?php if(!$is_basic_user && !empty($advance_categories)): ?>
      <section id="Bnr" style="margin-bottom: 0;">
        <h2 style="margin-bottom: 10px;">Advance</h2>
        <ul>
          <?php foreach($advance_categories as $category) : ?>
          <?php
            $category_id = $category['category_id'];
            $category_progress = isset($category_progress_map[$category_id]) ? $category_progress_map[$category_id] : array();
            $category_completion_rate = isset($category_progress['completion_rate']) ? (int)$category_progress['completion_rate'] : 0;
            $category_completed = isset($category_progress['completed_contents']) ? (int)$category_progress['completed_contents'] : 0;
            $category_total = isset($category_progress['total_contents']) ? (int)$category_progress['total_contents'] : 0;
            $progress_width = max(0, min(100, $category_completion_rate));
          ?>
          <li class="Hv">
            <a href="list.php?ctg_id=<?php echo $category['category_id']; ?>" style="position: relative; display: block;">
              <?php
                $top = $category['category_top_img'];
                $pi = pathinfo($top);
                $base = $pi['dirname'] . '/' . (isset($pi['filename']) ? $pi['filename'] : '');
                $jpg640 = $base . '_640.jpg';
                $jpg1280 = $base . '_1280.jpg';
                $webp640 = $base . '_640.webp';
                $webp1280 = $base . '_1280.webp';
                // 実在チェック用にファイルシステムパスへ変換
                $rootDir = dirname(__FILE__);
                $exists_webp640 = cached_file_exists($rootDir . '/' . ltrim($webp640, '/'));
                $exists_webp1280 = cached_file_exists($rootDir . '/' . ltrim($webp1280, '/'));
                $exists_jpg640 = cached_file_exists($rootDir . '/' . ltrim($jpg640, '/'));
                $exists_jpg1280 = cached_file_exists($rootDir . '/' . ltrim($jpg1280, '/'));
              ?>
              <picture>
                <?php if ($exists_webp640 || $exists_webp1280): ?>
                <source type="image/webp" srcset="<?php echo $exists_webp640 ? htmlspecialchars($webp640, ENT_QUOTES, 'UTF-8').' 640w' : ''; ?><?php echo ($exists_webp640 && $exists_webp1280) ? ', ' : ''; ?><?php echo $exists_webp1280 ? htmlspecialchars($webp1280, ENT_QUOTES, 'UTF-8').' 1280w' : ''; ?>" sizes="(max-width: 768px) 640px, 1280px">
                <?php endif; ?>
                <?php if ($exists_jpg640 || $exists_jpg1280): ?>
                <source type="image/jpeg" srcset="<?php echo $exists_jpg640 ? htmlspecialchars($jpg640, ENT_QUOTES, 'UTF-8').' 640w' : ''; ?><?php echo ($exists_jpg640 && $exists_jpg1280) ? ', ' : ''; ?><?php echo $exists_jpg1280 ? htmlspecialchars($jpg1280, ENT_QUOTES, 'UTF-8').' 1280w' : ''; ?>" sizes="(max-width: 768px) 640px, 1280px">
                <?php endif; ?>
                <img src="<?php echo htmlspecialchars($top, ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo date('His'); ?>" width="749" height="172" loading="lazy" decoding="async" alt=""/>
              </picture>
              <div style="position: absolute; left: 0; bottom: 0; width: 100%; padding: 12px 20px 14px; background: linear-gradient(0deg, rgba(0, 0, 0, 0.65), rgba(0, 0, 0, 0)); box-sizing: border-box;">
                <div style="width: 100%; margin: 0 auto;">
                  <div style="display: flex; justify-content: space-between; color: #fff; font-size: 12px; margin-bottom: 6px;">
                    <span><?php echo $category_completion_rate; ?>% 達成</span>
                    <span><?php echo $category_completed; ?> / <?php echo $category_total; ?></span>
                  </div>
                  <div style="position: relative; height: 8px; border-radius: 4px; background: rgba(255,255,255,0.25); overflow: hidden;">
                    <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 100%; border-radius: 4px; background: rgba(0,0,0,0.18);"></div>
                    <div style="position: absolute; left: 2px; right: 2px; top: 2px; bottom: 2px;">
                      <div style="width: <?php echo $progress_width; ?>%; height: 100%; border-radius: 4px; background: linear-gradient(90deg, var(--color-progress-start), var(--color-progress-end));"></div>
                    </div>
                  </div>
                </div>
              </div>
            </a>
          </li>
          <?php endforeach;?>
        </ul>
      </section>
      <?php endif; ?>

      <?php if(!empty($basic_categories)): ?>
      <section id="Bnr" style="margin-bottom: 8%;">
        <h2 style="margin-bottom: 10px;">Basic</h2>
        <ul>
          <?php foreach($basic_categories as $category) : ?>
          <?php
            $category_id = $category['category_id'];
            $category_progress = isset($category_progress_map[$category_id]) ? $category_progress_map[$category_id] : array();
            $category_completion_rate = isset($category_progress['completion_rate']) ? (int)$category_progress['completion_rate'] : 0;
            $category_completed = isset($category_progress['completed_contents']) ? (int)$category_progress['completed_contents'] : 0;
            $category_total = isset($category_progress['total_contents']) ? (int)$category_progress['total_contents'] : 0;
            $progress_width = max(0, min(100, $category_completion_rate));
          ?>
          <li class="Hv">
            <a href="list.php?ctg_id=<?php echo $category['category_id']; ?>" style="position: relative; display: block;">
              <?php
                $top = $category['category_top_img'];
                $pi = pathinfo($top);
                $base = $pi['dirname'] . '/' . (isset($pi['filename']) ? $pi['filename'] : '');
                $jpg640 = $base . '_640.jpg';
                $jpg1280 = $base . '_1280.jpg';
                $webp640 = $base . '_640.webp';
                $webp1280 = $base . '_1280.webp';
                // 実在チェック用にファイルシステムパスへ変換
                $rootDir = dirname(__FILE__);
                $exists_webp640 = cached_file_exists($rootDir . '/' . ltrim($webp640, '/'));
                $exists_webp1280 = cached_file_exists($rootDir . '/' . ltrim($webp1280, '/'));
                $exists_jpg640 = cached_file_exists($rootDir . '/' . ltrim($jpg640, '/'));
                $exists_jpg1280 = cached_file_exists($rootDir . '/' . ltrim($jpg1280, '/'));
              ?>
              <picture>
                <?php if ($exists_webp640 || $exists_webp1280): ?>
                <source type="image/webp" srcset="<?php echo $exists_webp640 ? htmlspecialchars($webp640, ENT_QUOTES, 'UTF-8').' 640w' : ''; ?><?php echo ($exists_webp640 && $exists_webp1280) ? ', ' : ''; ?><?php echo $exists_webp1280 ? htmlspecialchars($webp1280, ENT_QUOTES, 'UTF-8').' 1280w' : ''; ?>" sizes="(max-width: 768px) 640px, 1280px">
                <?php endif; ?>
                <?php if ($exists_jpg640 || $exists_jpg1280): ?>
                <source type="image/jpeg" srcset="<?php echo $exists_jpg640 ? htmlspecialchars($jpg640, ENT_QUOTES, 'UTF-8').' 640w' : ''; ?><?php echo ($exists_jpg640 && $exists_jpg1280) ? ', ' : ''; ?><?php echo $exists_jpg1280 ? htmlspecialchars($jpg1280, ENT_QUOTES, 'UTF-8').' 1280w' : ''; ?>" sizes="(max-width: 768px) 640px, 1280px">
                <?php endif; ?>
                <img src="<?php echo htmlspecialchars($top, ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo date('His'); ?>" width="749" height="172" loading="lazy" decoding="async" alt=""/>
              </picture>
              <div style="position: absolute; left: 0; bottom: 0; width: 100%; padding: 12px 20px 14px; background: linear-gradient(0deg, rgba(0, 0, 0, 0.65), rgba(0, 0, 0, 0)); box-sizing: border-box;">
                <div style="width: 100%; margin: 0 auto;">
                  <div style="display: flex; justify-content: space-between; color: #fff; font-size: 12px; margin-bottom: 6px;">
                    <span><?php echo $category_completion_rate; ?>% 達成</span>
                    <span><?php echo $category_completed; ?> / <?php echo $category_total; ?></span>
                  </div>
                  <div style="position: relative; height: 8px; border-radius: 4px; background: rgba(255,255,255,0.25); overflow: hidden;">
                    <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 100%; border-radius: 4px; background: rgba(0,0,0,0.18);"></div>
                    <div style="position: absolute; left: 2px; right: 2px; top: 2px; bottom: 2px;">
                      <div style="width: <?php echo $progress_width; ?>%; height: 100%; border-radius: 4px; background: linear-gradient(90deg, var(--color-progress-start), var(--color-progress-end));"></div>
                    </div>
                  </div>
                </div>
              </div>
            </a>
          </li>
          <?php endforeach;?>
        </ul>
      </section>
      <?php endif; ?>
    </div><!-- /Main -->

    <div id="Side"><!-- Side -->
      <?php include 'sidebar.php' ;?>
    </div><!-- /Side -->

  </div><!-- /Contents -->
<?php include 'tmp/footer.php';?>
</div><!-- /Wrapper -->

<script src="common/js/smoothscroll.js"></script>
<?php if($show_login_splash): ?>
<script>
  (function(){
    var splash = document.getElementById('loginSplash');
    if(!splash) return;
    setTimeout(function(){
      splash.style.pointerEvents = 'none';
    }, 400);
    setTimeout(function(){
      if(splash.parentNode){
        splash.parentNode.removeChild(splash);
      }
    }, 3700);
  })();
</script>
<?php endif; ?>
</body>
</html>