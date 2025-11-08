<?php
  require_once dirname(__FILE__) . '/scripts/env.php';
  require_once dirname(__FILE__) . '/scripts/Session.class.php';
  require_once dirname(__FILE__) . '/scripts/model/MemberModel.class.php';
  require_once dirname(__FILE__) . '/scripts/model/CategoryModel.class.php';
  require_once dirname(__FILE__) . '/scripts/model/ContentModel.class.php';
  require_once dirname(__FILE__) . '/scripts/model/MemberContentRelation.class.php';
  require_once dirname(__FILE__) . '/scripts/model/CommentModel.class.php';
  require_once dirname(__FILE__) . '/scripts/model/ContentVideoModel.class.php';
  
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
  
  // 閲覧権限チェック
  $has_permission = false;
  // target_courseが'all'またはNULLの場合は常に表示（旧来のコンテンツ）
  if (empty($content['target_course']) || $content['target_course'] === ContentModel::TARGET_COURSE_ADVANCE) {
      $has_permission = true;
  } elseif ($course_filter === ContentModel::TARGET_COURSE_ADVANCE) {
      // アドバンス会員は全コンテンツ表示
      $has_permission = true;
  } elseif ($content['target_course'] === $course_filter) {
      // ベーシック会員はベーシックコンテンツのみ表示
      $has_permission = true;
  }

  if (!$has_permission) {
    header("Location: index.php");
    exit;
  }

  // コンテンツ動画取得
  $content_video_model = new ContentVideoModel();
  $content_videos = $content_video_model->getVideosByContentId($content['content_id']);

  // カテゴリー取得
  $category_model = new CategoryModel();
  $category = $category_model->select(array('category_id'=>$content['category_id']));
  $category = $category[0];

  // カテゴリーに紐づくコンテンツ取得（その他の授業表示用）
  $where_other_contents = array(
    'category_id' => $category['category_id'],
    'indicate_flag' => ContentModel::ACTIVE,
  );
  if ($course_filter !== null) {
    $where_other_contents['target_course'] = $course_filter;
  }
  $content_list = $content_model->select($where_other_contents, array('content_week'=>ContentModel::ORDER_ASC));


  // コメント取得
  $comment_model = new CommentModel();
  $comment_list = $comment_model->select(array(
    'content_id'=>$content['content_id']
  ), array('comment_id'=>$comment_model::ORDER_DESC));
  $success_message = '';

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
        header("Location:/detail.php?". $_SERVER['QUERY_STRING']);
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
<link rel="apple-touch-icon" href="/common/img/apple-touch-icon.png">
<link href="common/css/main.css?date=20170614220000" rel="stylesheet">
<link href="common/css/jquery.circliful.css" rel="stylesheet" type="text/css" />
<link href="https://fonts.googleapis.com/css?family=Questrial" rel="stylesheet">
<style>
  .video-player-container {
    margin-bottom: 20px;
  }
  .video-thumbnail-list {
    display: flex;
    flex-wrap: nowrap; /* 強制的に1行で表示 */
    overflow-x: auto; /* 横スクロールを可能にする */
    gap: 10px;
    margin-top: 15px;
    padding-bottom: 10px; /* スクロールバーがコンテンツにかぶらないように */
  }
  .video-thumbnail-item {
    flex-shrink: 0; /* 縮小しない */
    cursor: pointer;
    border: 2px solid transparent;
    border-radius: 5px;
    overflow: hidden;
    width: 120px; /* サムネイルの幅を調整 */
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
  }
  .video-thumbnail-item:hover,
  .video-thumbnail-item.active {
    border-color: #007bff; /* アクティブなサムネイルのボーダー色 */
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
  }
  .video-thumbnail-item img {
    width: 100%;
    height: 70px; /* サムネイルの高さ */
    object-fit: cover;
    display: block;
  }
  .video-thumbnail-item span {
    display: -webkit-box; /* 複数行 ellipsis のために必要 */
    line-clamp: 2; /* 標準プロパティ */
    -webkit-line-clamp: 2; /* 2行で切り詰める */
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    padding: 5px;
    font-size: 0.8em;
    text-align: center;
    background-color: #f0f0f0;
    color: #333;
    min-height: 3em; /* 2行分の高さを確保 */
  }
  .youtube {
    position: relative;
    width: 100%;
    padding-top: 56.25%; /* 16:9 Aspect Ratio */
    height: 0;
    transition: opacity 0.5s ease-in-out; /* フェードアニメーション */
  }
  /* 複数動画用のパネル（同一DOM内で表示切替） */
  #main-video-player { position: relative; }
  .video-pane { display: none; position: absolute; inset: 0; }
  .video-pane.active { display: block; }
  .youtube.fade-out {
    opacity: 0;
  }
  .youtube.fade-in {
    opacity: 1;
  }
</style>
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="common/js/jquery.circliful.min.js"></script>
<script src="common/js/complete.js?=<?php echo time(); ?>"></script>
<!-- 動画切り替えスクリプト -->
<script>
  $(document).ready(function() {
    const player = $('#main-video-player');
    const panes = {}; // index -> pane div (.video-pane)
    let currentIndex = 0;

    // 初期のiframeを包んでパネル化（再ロードなし）
    const initialChild = player.children().first();
    if (initialChild.length) {
      const pane0 = $('<div class="video-pane active"></div>');
      pane0.append(initialChild); // 既存ノードをラップ（再読み込みしない）
      player.empty().append(pane0);
      panes[0] = pane0;
    }

    function getOrCreatePane(index) {
      if (panes[index]) return panes[index];
      const html = $('.video-thumbnail-item[data-index="' + index + '"]').data('video-url');
      const temp = $('<div></div>').html(html);
      const node = temp.contents(); // iframe等
      const pane = $('<div class="video-pane"></div>').append(node);
      player.append(pane); // 同一DOM下に保持（表示切替のみ）
      panes[index] = pane;
      return pane;
    }

    $('.video-thumbnail-item').on('click', function() {
      const index = parseInt($(this).data('index'), 10);
      if (isNaN(index) || index === currentIndex) return;

      const nextPane = getOrCreatePane(index);
      const currPane = panes[currentIndex];

      // フェード切替（DOMは保持、表示だけ切替）
      player.addClass('fade-out');
      setTimeout(() => {
        if (currPane) currPane.removeClass('active');
        nextPane.addClass('active');
        player.removeClass('fade-out').addClass('fade-in');
        setTimeout(() => player.removeClass('fade-in'), 500);
      }, 500);

      currentIndex = index;
      $('.video-thumbnail-item').removeClass('active');
      $(this).addClass('active');
    });
  });
</script>
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
        <?php
          $category_title = !empty($category['category_title'])
            ? $category['category_title']
            : 'Lesson' . $category['category_number'];
          $use_week = !isset($category['use_week_flag']) || (int)$category['use_week_flag'] === 1;
          $should_show_week = $use_week && !empty($content['content_week']);
        ?>
        <h2>
          <span><?php echo htmlspecialchars($category_title, ENT_QUOTES, 'UTF-8'); ?></span>
          <?php if ($should_show_week): ?>
            Week<?php echo htmlspecialchars($content['content_week'], ENT_QUOTES, 'UTF-8'); ?> :
          <?php endif; ?>
          <?php echo $content['content_title']; ?>
        </h2>
        <div class="Block">
          <?php if($has_permission): ?>
            <?php if(!empty($content_videos)) : ?>
            <div class="video-player-container">
              <div id="main-video-player" class="youtube">
                <?php echo $content_videos[0]['video_url']; // 最初の動画を初期表示 ?>
              </div>
              <div id="video-cache" style="display:none;"></div>
              <?php if(count($content_videos) > 1) : // 動画が複数ある場合のみ切り替えUIを表示 ?>
              <div class="video-thumbnail-list">
                <?php foreach($content_videos as $index => $video) : ?>
                <div class="video-thumbnail-item<?php echo ($index === 0) ? ' active' : ''; ?>" data-index="<?php echo $index; ?>" data-video-url="<?php echo htmlspecialchars($video['video_url'], ENT_QUOTES, 'UTF-8'); ?>">
                  <?php
                    $thumb = '';
                    if (!empty($video['thumbnail_url'])) {
                      $thumb = $video['thumbnail_url'];
                    } elseif (!empty($content['thumbnail_url'])) {
                      $thumb = $content['thumbnail_url'];
                    } else {
                      $thumb = 'common/img/no_image.png';
                    }
                    $pi = pathinfo($thumb);
                    $base = $pi['dirname'] . '/' . $pi['filename'];
                    $ext = isset($pi['extension']) ? $pi['extension'] : 'jpg';
                    $jpg320 = $base . '_320.jpg';
                    $jpg640 = $base . '_640.jpg';
                    $jpg1280 = $base . '_1280.jpg';
                    $webp320 = $base . '_320.webp';
                    $webp640 = $base . '_640.webp';
                    $webp1280 = $base . '_1280.webp';
                    $rootDir = dirname(__FILE__);
                    $exists_webp320 = file_exists($rootDir . '/' . ltrim($webp320, '/'));
                    $exists_webp640 = file_exists($rootDir . '/' . ltrim($webp640, '/'));
                    $exists_webp1280 = file_exists($rootDir . '/' . ltrim($webp1280, '/'));
                    $exists_jpg320 = file_exists($rootDir . '/' . ltrim($jpg320, '/'));
                    $exists_jpg640 = file_exists($rootDir . '/' . ltrim($jpg640, '/'));
                    $exists_jpg1280 = file_exists($rootDir . '/' . ltrim($jpg1280, '/'));
                  ?>
                  <picture>
                    <?php if ($exists_webp320 || $exists_webp640 || $exists_webp1280): ?>
                    <source type="image/webp" srcset="<?php
                      $ss = array();
                      if ($exists_webp320) $ss[] = htmlspecialchars($webp320, ENT_QUOTES, 'UTF-8')." 320w";
                      if ($exists_webp640) $ss[] = htmlspecialchars($webp640, ENT_QUOTES, 'UTF-8')." 640w";
                      if ($exists_webp1280) $ss[] = htmlspecialchars($webp1280, ENT_QUOTES, 'UTF-8')." 1280w";
                      echo implode(', ', $ss);
                    ?>" sizes="(max-width: 768px) 320px, 120px">
                    <?php endif; ?>
                    <?php if ($exists_jpg320 || $exists_jpg640 || $exists_jpg1280): ?>
                    <source type="image/jpeg" srcset="<?php
                      $ss = array();
                      if ($exists_jpg320) $ss[] = htmlspecialchars($jpg320, ENT_QUOTES, 'UTF-8')." 320w";
                      if ($exists_jpg640) $ss[] = htmlspecialchars($jpg640, ENT_QUOTES, 'UTF-8')." 640w";
                      if ($exists_jpg1280) $ss[] = htmlspecialchars($jpg1280, ENT_QUOTES, 'UTF-8')." 1280w";
                      echo implode(', ', $ss);
                    ?>" sizes="(max-width: 768px) 320px, 120px">
                    <?php endif; ?>
                    <img src="<?php echo htmlspecialchars($thumb, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($video['video_title'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" decoding="async" width="120" height="70" style="width:120px;height:70px;object-fit:cover;">
                  </picture>
                  <span><?php echo htmlspecialchars($video['video_title'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>
            </div>
            <?php else: ?>
            <?php if(isset($content['content_movie_url'])) : ?>
            <div class="youtube">
              <?php echo $content['content_movie_url']; // 従来の単一動画表示 ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
            <?php if(!empty($content['content_text'])): ?>
            <div class="txtBlock">
              <?php echo $content['content_text']; ?>
            </div>
            <?php endif; ?>
            <?php if(!empty($content['text_dl_url']) || !empty($content['message_dl_url'])): ?>
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
            <?php endif; ?>
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
          <?php else: ?>
            <div class="Block" style="text-align: center; padding: 50px 20px; background-color: #fff; border: 1px solid #ddd; border-radius: 5px;">
              <p style="font-size: 1.2em; color: #f44336; margin-bottom: 20px;">このコンテンツを閲覧する権限がありません。</p>
              <p>あなたの契約コースでは、このコンテンツは利用できません。詳細については、サポートにお問い合わせください。</p>
              <p style="margin-top: 20px;"><a href="index.php" class="Btn">トップページに戻る</a></p>
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
        <?php if($has_permission): // 権限がある場合のみコメントフォームを表示 ?>
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
        <?php else: ?>
          <p style="text-align: center; color: #999;">このコンテンツへのコメントは、閲覧権限のある方のみ可能です。</p>
        <?php endif; ?>
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
                      <?php
                        $oth = $cont['thumbnail_url'];
                        $pi2 = pathinfo($oth);
                        $base2 = $pi2['dirname'] . '/' . $pi2['filename'];
                        $jpg640_2 = $base2 . '_640.jpg';
                        $jpg1280_2 = $base2 . '_1280.jpg';
                        $webp640_2 = $base2 . '_640.webp';
                        $webp1280_2 = $base2 . '_1280.webp';
                        $rootDir = dirname(__FILE__);
                        $ex_w640 = file_exists($rootDir . '/' . ltrim($webp640_2, '/'));
                        $ex_w1280 = file_exists($rootDir . '/' . ltrim($webp1280_2, '/'));
                        $ex_j640 = file_exists($rootDir . '/' . ltrim($jpg640_2, '/'));
                        $ex_j1280 = file_exists($rootDir . '/' . ltrim($jpg1280_2, '/'));
                      ?>
                      <picture>
                        <?php if ($ex_w640 || $ex_w1280): ?>
                        <source type="image/webp" srcset="<?php echo $ex_w640 ? htmlspecialchars($webp640_2, ENT_QUOTES, 'UTF-8').' 640w' : ''; ?><?php echo ($ex_w640 && $ex_w1280) ? ', ' : ''; ?><?php echo $ex_w1280 ? htmlspecialchars($webp1280_2, ENT_QUOTES, 'UTF-8').' 1280w' : ''; ?>" sizes="(max-width: 768px) 320px, 640px">
                        <?php endif; ?>
                        <?php if ($ex_j640 || $ex_j1280): ?>
                        <source type="image/jpeg" srcset="<?php echo $ex_j640 ? htmlspecialchars($jpg640_2, ENT_QUOTES, 'UTF-8').' 640w' : ''; ?><?php echo ($ex_j640 && $ex_j1280) ? ', ' : ''; ?><?php echo $ex_j1280 ? htmlspecialchars($jpg1280_2, ENT_QUOTES, 'UTF-8').' 1280w' : ''; ?>" sizes="(max-width: 768px) 320px, 640px">
                        <?php endif; ?>
                        <img src="<?php echo htmlspecialchars($oth, ENT_QUOTES, 'UTF-8'); ?>" width="217" height="150" loading="lazy" decoding="async" alt=""/>
                      </picture>
                    <div class="txt">
                      <?php if ($use_week && !empty($cont['content_week'])): ?>
                      <div class="number">Week <?php echo $cont['content_week']; ?></div>
                      <?php endif; ?>
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
      <?php
        $category_title = !empty($category['category_title'])
          ? $category['category_title']
          : 'Lesson' . $category['category_number'];
      ?>
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