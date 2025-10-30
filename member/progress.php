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

  $member_id = $session->get('member');
  
  // 会員モデルとコンテンツモデルを初期化
  $member_model = new MemberModel();
  $content_model = new ContentModel();
  
  // 会員情報を取得
  $member_info = $member_model->select(array('member_id' => $member_id));
  if (empty($member_info)) {
      header("Location: login.php");
      exit;
  }
  $member_info = $member_info[0];
  
  // 会員のコース別進捗情報を取得
  $member_course_progress = $member_model->getMemberCourseProgress($member_id);
  $total_contents = $member_course_progress['total_contents'];
  $completed_contents = $member_course_progress['completed_contents'];
  $completion_rate = $member_course_progress['completion_rate'];
  $progress_data = $member_course_progress['progress_data'];

  // 全てのカテゴリー取得
  $category_model = new CategoryModel();
  $category_list = $category_model->select(array('indicate_flag'=>1), array('category_number'=>$category_model::ORDER_ASC));
  
  $categories_with_progress = [];
  foreach ($category_list as $category) {
    $category_id = $category['category_id'];
    $category_progress = $member_model->getMemberCourseProgress($member_id, $category_id);
    $category['total_contents'] = $category_progress['total_contents'];
    $category['completed_contents'] = $category_progress['completed_contents'];
    $category['completion_rate'] = $category_progress['completion_rate'];
    $categories_with_progress[] = $category;
  }

?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" >
<meta name="robots" content="noindex,nofollow" />
<meta name="viewport" content="width=device-width">
<title>学習進捗 | THE Imagine Membersサイト</title>
<link rel="apple-touch-icon" href="/common/img/apple-touch-icon.png">
<link href="common/css/main.css?date=201707132215" rel="stylesheet">
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
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
<div id="wrapper"><!-- Wrapper -->
  <div id="ContentsMrg">
    <!-- Contents -->
    <div id="Main">
      <!-- Main -->
      <section id="ProgressDetail" class="progress-section">
        <h2>学習進捗詳細</h2>
        <p>現在のコース: <strong><?php echo htmlspecialchars($member_model->getMemberCourseName($member_info['select_course'])); ?></strong></p>
        <p>全体の完了率: <strong><?php echo $completion_rate; ?>%</strong> (<?php echo $completed_contents; ?> / <?php echo $total_contents; ?> コンテンツ)</p>
        <div class="progress-bar-container">
          <div class="progress-bar-fill" style="width: <?php echo $completion_rate; ?>%;"></div>
          <span class="progress-text-overlay"><?php echo $completion_rate; ?>%</span>
        </div>

        <h3>カテゴリー別進捗</h3>
        <ul class="category-progress-list">
          <?php foreach ($categories_with_progress as $category): ?>
            <li>
              <h4><?php echo htmlspecialchars($category['category_name']); ?> (<?php echo $category['completion_rate']; ?>% 完了)</h4>
              <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width: <?php echo $category['completion_rate']; ?>%;"></div>
                <span class="progress-text-overlay"><?php echo $category['completion_rate']; ?>%</span>
              </div>
              <button class="expand-button" onclick="toggleContentDetails(this)">詳細を表示</button>
              <div class="content-details">
                <?php 
                  $category_progress_data = $member_model->getMemberProgressByCourse($member_id, $category['category_id']);
                ?>
                <?php if(!empty($category_progress_data)): ?>
                  <?php foreach ($category_progress_data as $content): ?>
                    <div class="content-list-item">
                      <span><?php echo htmlspecialchars($content['content_title']); ?></span>
                      <span class="status <?php echo $content['is_completed'] ? 'completed' : 'pending'; ?>">
                        <?php echo $content['is_completed'] ? '✅ 完了' : '未完了'; ?>
                      </span>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <p>このカテゴリーにはコンテンツがありません。</p>
                <?php endif; ?>
              </div>
            </li>
          <?php endforeach; ?>
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
