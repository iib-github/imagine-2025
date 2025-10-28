<?php
  require_once dirname(__FILE__) . '/scripts/Session.class.php';
  require_once dirname(__FILE__) . '/scripts/model/CategoryModel.class.php';
  require_once dirname(__FILE__) . '/scripts/model/NewsModel.class.php';
  $session = Session::getInstance();

  // お知らせの取得数
  const NEWS_NUM = 5;

  // セッションがなければログイン画面に遷移させる。
  if($session->get('member') === false) {
    header("Location: login.php");
    exit;
  }

  // 有効なカテゴリーを全て取得
  $category_model = new CategoryModel();
  $category_list = $category_model->select(array('indicate_flag'=>1), array('category_number'=>$category_model::ORDER_ASC));

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
<link rel="apple-touch-icon" href="/membership/member/common/img/apple-touch-icon.png">
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