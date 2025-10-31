<?php
  require_once dirname(__FILE__) . '/scripts/Session.class.php';
  require_once dirname(__FILE__) . '/scripts/model/TagModel.class.php';
  require_once dirname(__FILE__) . '/scripts/model/ContentModel.class.php';
  require_once dirname(__FILE__) . '/scripts/model/MemberModel.class.php'; // コースフィルタリングのため
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

  $tag_model = new TagModel();
  $all_tags = $tag_model->getTagList(null, array('tag_name' => BaseModel::ORDER_ASC));

  $content_model = new ContentModel();
  $search_results = array();
  $selected_tag_ids = array();

  if($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['tags'])) {
    $selected_tag_ids = array_map('intval', (array)$_GET['tags']);
    
    if(!empty($selected_tag_ids)) {
      $where_conditions = array(
        'indicate_flag' => ContentModel::ACTIVE,
      );
      
      // コースフィルタを適用
      if ($course_filter !== null) {
        $where_conditions['target_course'] = $course_filter;
      }

      $search_results = $content_model->getContentListByTags($selected_tag_ids, $where_conditions, array('content_week' => BaseModel::ORDER_ASC));
    }
  }

?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" >
<title>タグ検索 | THE Imagine Members SITE</title>
<meta name="robots" content="noindex,nofollow" />
<meta name="viewport" content="width=device-width">
<link rel="apple-touch-icon" href="/common/img/apple-touch-icon.png">
<link href="common/css/main.css" rel="stylesheet">
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<script src="common/js/respond.min.js"></script>
<![endif]-->
<!--[if IE 6]><script src="common/js/minmax.js"></script><![endif]-->
<?php include 'tmp/analytics.php';?>
<style>
  .tag-checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    background-color: #f9f9f9;
    border: 1px solid #eee;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
  }
  .tag-checkbox-item {
    display: flex;
    align-items: center;
  }
  .tag-checkbox-item input[type="checkbox"] {
    margin-right: 8px;
  }
  .tag-checkbox-item input[type="checkbox"]:checked + label {
    background-color: #007bff;
    color: white;
    padding: 3px 8px;
    border-radius: 3px;
  }
  .tag-actions {
    margin-bottom: 15px;
    text-align: right;
  }
  .tag-actions button {
    background-color: #6c757d;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 3px;
    cursor: pointer;
    font-size: 0.9em;
    margin-left: 10px;
  }
  .tag-actions button:hover {
    background-color: #5a6268;
  }
  .search-button-area {
    text-align: center;
    margin-bottom: 30px;
    margin-top: 20px; /* タグアクションボタンとの間に余白 */
  }
  .search-button-area input[type="submit"] {
    background-color: #007bff;
    color: white;
    padding: 10px 25px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1.1em;
  }
  .search-button-area input[type="submit"]:hover {
    background-color: #0056b3;
  }
  .search-results-list {
    list-style: none;
    padding: 0;
  }
  .search-results-list li {
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 15px;
    padding: 15px;
    display: flex;
    align-items: center;
  }
  .search-results-list li .thumb {
    flex-shrink: 0;
    margin-right: 15px;
  }
  .search-results-list li .thumb img {
    width: 100px;
    height: 70px;
    object-fit: cover;
    border-radius: 3px;
  }
  .search-results-list li .txt {
    flex-grow: 1;
  }
  .search-results-list li .title {
    font-weight: bold;
    font-size: 1.2em;
    margin-bottom: 5px;
  }
  .search-results-list li .category-info {
    font-size: 0.9em;
    color: #666;
    margin-bottom: 5px;
  }
  .search-results-list li .summery {
    font-size: 0.9em;
    color: #555;
  }
</style>
</head>

<body>
<?php include 'tmp/header.php';?>
<div id="wrapper">
  <!-- Wrapper -->
  <div id="ContentsMrg">
    <!-- Contents -->
    <div id="Main">
      <!-- Main -->
      <section id="TagSearch">
        <h2>タグでコンテンツを探す</h2>
        <form method="GET" action="tag-search.php">
          <div class="tag-actions">
            <button type="button" id="selectAllTags">全て選択</button>
            <button type="button" id="deselectAllTags">全て解除</button>
          </div>
          <div class="tag-checkbox-group">
            <?php if(!empty($all_tags)): ?>
              <?php foreach($all_tags as $tag): ?>
                <div class="tag-checkbox-item">
                  <input type="checkbox" name="tags[]" value="<?php echo htmlspecialchars($tag['tag_id']); ?>" id="tag_<?php echo htmlspecialchars($tag['tag_id']); ?>"
                    <?php echo in_array($tag['tag_id'], $selected_tag_ids) ? 'checked' : ''; ?>>
                  <label for="tag_<?php echo htmlspecialchars($tag['tag_id']); ?>"><?php echo htmlspecialchars($tag['tag_name']); ?></label>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p>登録されているタグがありません。</p>
            <?php endif; ?>
          </div>
          <div class="search-button-area">
            <input type="submit" value="タグで検索">
          </div>
        </form>

        <?php if(!empty($selected_tag_ids) && empty($search_results)): ?>
          <p style="text-align: center; color: #f44336; font-weight: bold;">選択されたタグに一致するコンテンツは見つかりませんでした。</p>
        <?php endif; ?>

        <?php if(!empty($search_results)): ?>
          <h3>検索結果 (<?php echo count($search_results); ?>件)</h3>
          <ul class="search-results-list">
            <?php foreach($search_results as $content): ?>
              <li>
                <a href="detail.php?cont_id=<?php echo htmlspecialchars($content['content_id']); ?>">
                  <div class="thumb">
                    <img src="<?php echo !empty($content['thumbnail_url']) ? htmlspecialchars($content['thumbnail_url']) : 'common/img/no_image.png'; ?>" alt="<?php echo htmlspecialchars($content['content_title']); ?>">
                  </div>
                  <div class="txt">
                    <div class="category-info">Lesson <?php echo htmlspecialchars($category_model->getCategoryNumber($content['category_id'])); ?> / Week <?php echo htmlspecialchars($content['content_week']); ?></div>
                    <div class="title"><?php echo htmlspecialchars($content['content_title']); ?></div>
                    <div class="summery"><?php echo htmlspecialchars(strip_tags($content['content_text'])); ?></div>
                  </div>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php elseif(empty($selected_tag_ids)): ?>
          <p style="text-align: center; color: #555;">タグを選択してコンテンツを検索してください。</p>
        <?php endif; ?>
      </section>
    </div><!-- /Main -->
    <div id="Side"><!-- Side -->
      <?php include 'sidebar.php' ;?>
    </div><!-- /Side -->
  </div><!-- /Contents -->
<?php include 'tmp/footer.php';?>
</div><!-- /Wrapper -->

<script src="common/js/smoothscroll.js"></script>
<script>
  $(document).ready(function() {
    $('#selectAllTags').on('click', function() {
      $('.tag-checkbox-group input[type="checkbox"]').prop('checked', true);
    });

    $('#deselectAllTags').on('click', function() {
      $('.tag-checkbox-group input[type="checkbox"]').prop('checked', false);
    });
  });
</script>
</body>
</html>
