<?php
  require_once dirname(__FILE__) . '/scripts/Session.class.php';
  require_once dirname(__FILE__) . '/scripts/model/MemberModel.class.php';
  require_once dirname(__FILE__) . '/scripts/model/CategoryModel.class.php';
  require_once dirname(__FILE__) . '/scripts/model/ContentModel.class.php';
  require_once dirname(__FILE__) . '/scripts/model/MemberContentRelation.class.php';
  require_once dirname(__FILE__) . '/scripts/model/CommentModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('member') === false) {
    header("Location: login.php");
    exit;
  }

  // IEだとaタグのdownload属性が使えないのでJSでダウンロードさせる必要がある。
  $ieFlag = strstr($_SERVER['HTTP_USER_AGENT'], 'Trident') || strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE');

  // コンテンツ取得
  $content_model = new ContentModel();
  $content = $content_model->select(array(
    'content_id'=>$_GET['cont_id'],
    'indicate_flag'=>$content_model::ACTIVE,
  ));
  if(empty($content)) {
    header("Location: index.php");
    exit;
  } else {
    $content = $content[0];
  }

  // カテゴリー取得
  $category_model = new CategoryModel();
  $category = $category_model->select(array('category_id'=>$content['category_id']));
  $category = $category[0];

  // カテゴリーに紐づくコンテンツ取得（その他の授業表示用）
  $content_list = $content_model->select(array(
    'category_id' => $category['category_id'],
    'indicate_flag' => 1,
  ), array('content_week'=>$content_model::ORDER_ASC));


  // コメント取得
  $comment_model = new CommentModel();
  $comment_list = $comment_model->select(array(
    'content_id'=>$content['content_id']
  ), array('comment_id'=>$comment_model::ORDER_DESC));
  $success_message = '';

  // 会員一覧取得
  $member_model = new MemberModel();

  // 会員表示名とidの紐付け表を作成
  $member_list = $member_model->select();
  $number_list = array();
  foreach ($member_list as $mem) {
    $number_list[$mem['member_id']] = $mem['member_name'];
  }

  // サイドバー（レッスン一覧）表示用
  $category_list = $category_model->select(array('indicate_flag'=>1), array('category_number'=>$category_model::ORDER_ASC));

  // 達成済みのコンテンツかどうか
  $membContRel = new MemberContentRelation(1);
  $result = $membContRel->select(array(
    'member_id' => $session->get('member'),
    'content_id' => $content['content_id'],
  ));
  $is_finished = !empty($result);


  if($_SERVER["REQUEST_METHOD"] == "POST") {
    //一度投稿していないかチェック
    $errFlg = '';
    $comment_my_list = '';
    $comment_my_list = $comment_model->select(array('content_id'=>$content['content_id'],'member_id'=>$_POST['mid']));
    if (!empty($comment_my_list)) {
      $errFlg = true;
    }


    if(!$errFlg) {
      $success = $comment_model->registerComment(array(
        'member_id'=> $_POST['mid'],
        'name'=> $_POST['name'],
        'content_id'=> $content['content_id'],
        'comment' => $_POST['comment'],
      ));

      if($success) {
        $_SESSION['success_message'] = 'コメント投稿が完了しました。';
        header("Location:/member/detail.php?". $_SERVER['QUERY_STRING']);
        exit;
      } else {
        $_SESSION['success_message'] = 'コメント投稿に失敗しました。';
        exit;
      }
    } else {
      $_SESSION['success_message'] = '既にコメントを投稿している場合はコメントできません。';
    }
  }

?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" >
<title>コンテンツ詳細 - THE Imagine Members SITE</title>
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
<?php include 'tmp/header.php';?>
<div id="wrapper">
  <!-- Wrapper -->
  <div id="ContentsMrg">
    <!-- Contents -->
    <div id="Main">
      <!-- Main -->
      <section id="ContentsDetail">
        <?php if ($category['category_number'] == '12'): ?>
        <h2><span>イマジンラジオ</span><?php echo $content['content_title']; ?></h2>
        <?php elseif ($category['category_number'] == '11'): ?>
          <h2><span>QAライブ動画</span><?php echo $content['content_title']; ?></h2>
        <?php else: ?>
          <h2><span>Lesson<?php echo $category['category_number']; ?></span>Week<?php echo $content['content_week']; ?> :<?php echo $content['content_title']; ?></h2>
        <?php endif; ?>
        <div class="Block">
          <?php if(isset($content['content_movie_url'])) : ?>
          <div class="youtube">
            <?php echo $content['content_movie_url']; ?>
          </div>
          <?php endif; ?>
          <div class="txtBlock">
            <?php echo $content['content_text']; ?>
          </div>
          <div class="BtnBlock">
<?php if(!empty($content['text_dl_url'])):?>
            <a href="<?php echo $content['text_dl_url']; ?>" download="<?php echo $content['content_title']; ?>_ワーク.pdf" target="_blank">
              <div class="BlueBtn">講座資料をダウンロード</div>
            </a>
<?php endif; ?>
<?php if(!empty($content['message_dl_url'])):?>
            <a href="<?php echo $content['message_dl_url']; ?>" download="<?php echo $content['content_title']; ?>_書き起こし.pdf" target="_blank">
              <div class="GreenBtn">書き起こし資料をダウンロード</div>
            </a>
<?php endif; ?>
          </div>
          <?php if(!$is_finished): ?>
          <div class="BtnBlockSingle" id="complete">
            <a href="javascript:void(0);" onclick="complete(<?php echo $session->get('member').', '.$category['category_id'].', '.$content['content_id']; ?>);">
              <div class="RedBtn">この授業を完了にする</div>
            </a>
          </div>
          <?php else: ?>
          <div class="BtnBlockSingle" id="finished">
            <div class="GrayBtn">修了済み</div>
          </div>
        <?php endif; ?>
        </div>
      </section>


      <section id="Comments">
      <h2>コメント</h2>
      <div class="Block">
        <h3>コメントを投稿する</h3>
        <p class="midNote">コメントの中からピックアップしてFAQ配信を行いますので、ぜひ星野ワタルに聞きたいことをコメントしてください！（※コメントは1動画1回まで可能です。）</p>
        <?php if( empty($_POST['btn_submit']) && !empty($_SESSION['success_message']) ): ?>
          <p class="success_message red"><?php echo $_SESSION['success_message']; ?></p>
          <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <form class="MyAccount" method="POST" action='detail.php?cont_id=<?php echo($_GET['cont_id']);?>'>
          <input type="hidden" name="mid" value="<?php echo $session->get('member'); ?>">
          <dl>
            <dt>表示する名前　<span class="red"><sup>*</sup>必須</span></dt>
            <dd><input type="text" name="name" value="" required></dd>
          </dl>
          <dl>
            <dt>コメント　<span class="red"><sup>*</sup>必須</span></dt>
            <dd><textarea name="comment" rows="4" required></textarea></dd>
          </dl>
          <div class="EntBtn"><input type="submit" value="投稿する"></div>
        </form>
        <hr>
<?php if(!empty($comment_list)): ?>
      <ul class="comments">
      <?php foreach($comment_list as $com): ?>
        <li class="Hv">
          <div class="comment_list">
            <div class="name"><p><?php echo $com['name']; ?>さん</p></div>
            <div class="comment"><p><?php echo nl2br($com['comment']);?></p></div>
          </div>
        </li>
      <?php endforeach; ?>
       </ul>
<?php else: ?>
<?php endif; ?>
      </div>
       </section>


      <?php if(empty($content_list)): ?>
      <section id="Quests">
      <h2>その他の授業</h2>
      <ul>
      <?php foreach($content_list as $cont): ?>
      <?php if($cont['content_week'] == $content['content_week']) continue; ?>
                <li class="Hv">
                  <a href="detail.php?cont_id=<?php echo $cont['content_id']; ?>" style="display: inline;">
                    <div class="thumb">
                      <img src="<?php echo $cont['thumbnail_url']; ?>" width="217" height="150" alt=""/></div>
                    <div class="txt">
                      <div class="number">Week <?php echo $cont['content_week']; ?></div>
                      <div class="title"><?php echo $cont['content_title']; ?></div>
                      <div class="summery"><?php echo $cont['content_text']; ?></div>
                    </div>
                  </a>
                </li>
      <?php endforeach; ?>
       </ul>
       </section>
       <?php else: ?>
       <?php endif; ?>

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
<?php
  $member_model = new MemberModel();
  $score = $member_model->getScore($session->get('member'), $category['category_id']);
?>
  $( document ).ready(function() { // 6,32 5,38 2,34
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
<?php if($ieFlag): ?>
<script type="text/javascript">
var userAgent = window.navigator.userAgent.toLowerCase();
$(".dl-btn").click(function() {
  if($(this).hasClass("text-dl")) {
    var type = ["text", ".pdf"];
  } else {
    var type = ["voice", ".mp3"];
    var $target = $(this).find("div");
    $target.text("ダウンロード中です。");
    // $target.removeClass("BlueBtn");
    // $target.addClass("GrayBtn");
  }

  var xhr = new XMLHttpRequest();
  xhr.open('GET', '<?php echo $content['text_dl_url']; ?>');
  xhr.responseType = 'blob';
  xhr.onloadend = function() {
      if(xhr.status !== 200) return;
      window.navigator.msSaveBlob(xhr.response, '<?php echo $content['content_title']; ?>'+type[1]);
  }
  xhr.send();
});
</script>
<?php endif; ?>
<script src="common/js/smoothscroll.js"></script>
</body>
</html>