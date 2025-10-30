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
  
  $member_id = $session->get('member');
  // 会員モデルとコンテンツモデルを初期化
  $member_model = new MemberModel();
  $content_model = new ContentModel();
  
  // 会員情報を取得
  $member_info = $member_model->select(array('member_id' => $member_id));
  if (empty($member_info)) {
      // エラーハンドリング、またはログインページへリダイレクト
      header("Location: login.php");
      exit;
  }
  $member_info = $member_info[0];
  $course_filter = $member_model->getCourseFilter($member_info['select_course']);

  // 有効なカテゴリーを全て取得
  $category_model = new CategoryModel();
  $all_categories = $category_model->select(array('indicate_flag'=>1), array('category_number'=>$category_model::ORDER_ASC));
  $category_list = $category_model->filterCategoriesByCourse($all_categories, $course_filter);
  
  // 会員のコース別進捗情報を取得
  $member_progress = $member_model->getMemberCourseProgress($member_id);
  $total_contents = $member_progress['total_contents'];
  $completed_contents = $member_progress['completed_contents'];
  $completion_rate = $member_progress['completion_rate'];

  // お知らせ取得
  $news_model = new NewsModel();
  $news_list = $news_model->getNewsList(NEWS_NUM);

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

<body>
<section id="MV">
  <div class="Cnt">
    <h1><img src="common/img/login_logo.png" width="320" alt="THE Imagine"/></h1>
  </div>
</section>
<div id="wrapper"><!-- Wrapper -->
  <div id="Contents"><!-- Contents -->
    <div id="Main"><!-- Main -->
      <section id="Progress">
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
      </section>
      <section id="News">
        <h2>更新情報</h2>
        <div class="Block">
          <ul>
            <?php foreach ($news_list as $n): ?>
            <li>
              <a href="news-detail.php?id=<?php echo $n['id']?>">
              <dl>
                <dt><?php echo mb_substr($n['note_date'], 0, 10); ?></dt>
                <dd><?php echo $n['description']; ?></dd>
              </dl>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </section>
      <section id="Bnr" style="margin-bottom: 8%;">
        <ul>
          <?php foreach($category_list as $category) : ?>
          <li class="Hv">
            <a href="list.php?ctg_id=<?php echo $category['category_id']; ?>">
              <img src="<?php echo $category['category_top_img'] ;?>?=<?php echo date('His');?>" width="749" height="172" alt=""/>
            </a>
          </li>
          <?php endforeach;?>
        </ul>
      </section>
    </div><!-- /Main -->

    <div id="Side"><!-- Side -->
      <?php include 'sidebar.php' ;?>
    </div><!-- /Side -->

  </div><!-- /Contents -->
<?php include 'tmp/footer.php';?>
</div><!-- /Wrapper -->

<script src="common/js/smoothscroll.js"></script>
</body>
</html>