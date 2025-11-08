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
  $category_model = new CategoryModel();
  $search_results = array();
  $selected_tag_ids = array();

  if($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['tags'])) {
    $selected_tag_ids = array_map('intval', (array)$_GET['tags']);
    
    if(!empty($selected_tag_ids)) {
      $where_conditions = array(
        'indicate_flag' => ContentModel::ACTIVE,
      );
      
    // コースフィルタを適用（ベーシックは限定、アドバンスは全件）
    if ($course_filter === ContentModel::TARGET_COURSE_BASIC) {
      $where_conditions['target_course'] = ContentModel::TARGET_COURSE_BASIC;
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
</head>

<body>
<?php include 'tmp/header.php';?>
<div id="wrapper">
  <!-- Wrapper -->
  <div id="ContentsMrg">
    <!-- Contents -->
    <div id="Main">
      <!-- Main -->
      <form method="GET" action="tag-search.php">
        <section id="ContentsDetail">
          <h2>タグで動画を探す</h2>
          <div class="Block">
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
            <div class="tag-actions">
              <div class="tag-actions__left">
                <input type="submit" value="タグで検索" class="tag-actions__submit">
              </div>
              <div class="tag-actions__right">
                <button type="button" id="selectAllTags">全て選択</button>
                <button type="button" id="deselectAllTags">全て解除</button>
              </div>
            </div>
          </div>
        </section>
      </form>

      <section id="TagSearch">
        <?php if(!empty($selected_tag_ids) && empty($search_results)): ?>
          <p style="text-align: center; color: #f44336; font-weight: bold;">選択されたタグに一致するコンテンツは見つかりませんでした。</p>
        <?php endif; ?>

        <?php if(!empty($search_results)): ?>
          <?php $category_cache = array(); ?>
          <h3>検索結果 (<?php echo count($search_results); ?>件)</h3>
          <section id="Quests" class="SearchResults">
            <ul>
              <?php foreach($search_results as $content): ?>
              <li class="Hv">
                <a href="detail.php?cont_id=<?php echo $content['content_id']; ?>">
                  <div class="thumb">
                    <?php
                      $thumbnail = $content['thumbnail_url'];
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
                    <?php
                      $category_id = $content['category_id'];
                      if (!isset($category_cache[$category_id])) {
                        $category_result = $category_model->select(array('category_id' => $category_id));
                        $category_cache[$category_id] = !empty($category_result) ? $category_result[0] : null;
                      }
                      $category_info = $category_cache[$category_id];
                      $category_title = ($category_info && !empty($category_info['category_title'])) ? $category_info['category_title'] : 'カテゴリ未設定';
                    ?>
                    <div class="category-label">
                      <?php echo htmlspecialchars($category_title, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div class="number">Week <?php echo $content['content_week']; ?></div>
                    <div class="title"><?php echo htmlspecialchars($content['content_title'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="summery"><?php echo strip_tags($content['content_text']); ?></div>
                  </div>
                </a>
              </li>
              <?php endforeach; ?>
            </ul>
          </section>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
