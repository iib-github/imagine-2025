<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/ContentModel.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/CategoryModel.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/TagModel.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/ContentVideoModel.class.php';
  
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('admin') === false) {
    header("Location: login.php");
    exit;
  }

  // 全てのカテゴリー取得
  $category_model = new CategoryModel();
  $category_list = $category_model->select(null, array('category_number' => BaseModel::ORDER_ASC));
  
  // 全てのタグ取得
  $tag_model = new TagModel();
  $tag_list = $tag_model->getTagList(null, array('tag_name' => BaseModel::ORDER_ASC));
  
  // 利用可能なコース一覧を取得
  $content_model = new ContentModel();
  $available_courses = $content_model->getAvailableCourses();

  // コンテンツ登録時
  if($_SERVER["REQUEST_METHOD"] == "POST") {
    $content_data = array(
      'category_id' => $_POST['category'],
      'content_week' => $_POST['week'],
      'content_title' => $_POST['title'],
      'content_movie_url' => $_POST['movie_url'],
      'content_text' => $_POST['discription'],
      'display_order' => $_POST['order'],
      'indicate_flag' => $_POST['active'],
      'pub_date' => $_POST['pub_date'],
      'target_course' => isset($_POST['target_course']) ? $_POST['target_course'] : ContentModel::TARGET_COURSE_ALL,
    );
    if(isset($_POST['is_faq'])) {
      $content_data['is_faq'] = $_POST['is_faq'];
    } else {
      $content_data['is_faq'] = $content_model::IS_NOT_FAQ;
    }

    $success = $content_model->registerContent($content_data);
    if($success) {
      // コンテンツIDを取得
      $content_id = $content_model->lastInsertId();
      
      // タグを関連付け
      if(!empty($_POST['tags'])) {
        $tag_ids = array_map('intval', $_POST['tags']);
        $content_model->setContentTags($content_id, $tag_ids);
      }
      
      // 複数動画を登録
      if(!empty($_POST['video_urls'])) {
        $video_model = new ContentVideoModel();
        $display_order = 1;
        foreach($_POST['video_urls'] as $index => $video_url) {
          if(!empty($video_url)) {
            $video_data = array(
              'content_id' => $content_id,
              'video_url' => $video_url,
              'video_title' => isset($_POST['video_titles'][$index]) ? $_POST['video_titles'][$index] : '動画' . $display_order,
              'thumbnail_url' => isset($_POST['thumbnail_urls'][$index]) ? $_POST['thumbnail_urls'][$index] : '',
              'display_order' => $display_order
            );
            $video_model->registerVideo($video_data);
            $display_order++;
          }
        }
      }
      
      header("Location: list-content.php");
      exit;
    }

  }

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>コンテンツ登録 | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />
<style>
  .checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
  }
  .checkbox-item {
    display: flex;
    align-items: center;
  }
  .checkbox-item input[type="checkbox"] {
    margin-right: 8px;
  }
  .video-entry {
    border: 1px solid #ddd;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 4px;
    background-color: #f9f9f9;
  }
  .video-entry input,
  .video-entry textarea {
    width: 100%;
    margin-bottom: 10px;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 3px;
  }
  .remove-video-btn {
    background-color: #ff6b6b;
    color: white;
    padding: 8px 15px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-size: 12px;
  }
  .remove-video-btn:hover {
    background-color: #ff5252;
  }
  .add-video-btn {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    margin-bottom: 15px;
  }
  .add-video-btn:hover {
    background-color: #45a049;
  }
</style>
<script>
  function addVideoField() {
    const videoContainer = document.getElementById('video-container');
    const videoCount = videoContainer.children.length;
    
    const newEntry = document.createElement('div');
    newEntry.className = 'video-entry';
    newEntry.innerHTML = `
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <strong>動画 ${videoCount + 1}</strong>
        <button type="button" class="remove-video-btn" onclick="removeVideoField(this)">削除</button>
      </div>
      <input type="text" name="video_titles[]" placeholder="動画タイトル" style="width: 100%;">
      <textarea name="video_urls[]" placeholder="動画埋め込みコード（iframe等）" style="width: 100%; height: 80px;"></textarea>
      <input type="text" name="thumbnail_urls[]" placeholder="サムネイル画像URL" style="width: 100%;">
    `;
    
    videoContainer.appendChild(newEntry);
  }
  
  function removeVideoField(btn) {
    btn.closest('.video-entry').remove();
    // 番号を更新
    updateVideoNumbers();
  }
  
  function updateVideoNumbers() {
    const videoContainer = document.getElementById('video-container');
    const entries = videoContainer.querySelectorAll('.video-entry');
    entries.forEach((entry, index) => {
      const strong = entry.querySelector('strong');
      if(strong) {
        strong.textContent = '動画 ' + (index + 1);
      }
    });
  }
  
  window.addEventListener('load', function() {
    // 初期状態で1つの動画フィールドがない場合は追加
    const videoContainer = document.getElementById('video-container');
    if(videoContainer.children.length === 0) {
      addVideoField();
    }
  });
</script>
</head>

<body>

<!-- Wrapper starts -->
<div id="wrapper">
  <div class="InBox"><!-- INBOX -->
    <h1>コンテンツ登録</h1>

<?php
  $menu_active = 'cnts';
  include_once 'menu.php';
?>

    <form method="POST" action="register-content.php" enctype="multipart/form-data">
      <p><input type="submit" id="btnRegister" class="Btn" value="登録" name="register"></p>
      <table class="member">
        <tr>
          <th>紐づくカテゴリー</th>
          <td>
            <select name="category">
            <?php
              foreach ($category_list as $category) {
                if ($category['category_number'] == '12') {
                  echo '<option value="' . $category['category_id'] . '" >イマジンラジオ</option>';
                } elseif ($category['category_number'] == '11') {
                  echo '<option value="' . $category['category_id'] . '" >QAライブ動画</option>';
                } else {
                  echo '<option value="' . $category['category_id'] . '" >Lesson' . $category['category_number'] . '</option>';
                }
              }
            ?>
            </select>
          </td>
        </tr>
        <tr>
          <th>week</th>
          <td>
          <select name="week">
            <?php
            for ($i = 1; $i <= 20; $i++) {
              echo '<option value="'.$i.'">'.$i.'週目</option>';
            }
            ;?>
          </select>
        </tr>
        <tr>
        <th>コンテンツタイトル</th>
          <td><input type="text" name="title" style="width:800px;"></td>
        </tr>
        <tr>
          <th>サムネイル画像</th>
          <td>
            <input type="file" name="thumbnail" id="thumbnail">
          </td>
        </tr>
        <tr>
          <th>動画</th>
          <td>
            <button type="button" class="add-video-btn" onclick="addVideoField()">＋ 動画を追加</button>
            <div id="video-container"></div>
          </td>
        </tr>
        <tr>
          <th>説明テキスト</th>
          <td><textarea name="discription" id="colume" style="width:800px;height:100px;"></textarea></td>
        </tr>
        <tr>
          <th>講座資料ダウンロード</th>
          <td>
            <input type="file" name="txt_url" id="txt_url">
          </td>
        </tr>
        <tr>
          <th>文字起こし資料のダウンロード</th>
          <td>
            <input type="file" name="document" id="document">
          </td>
        </tr>
        <tr>
          <th>一覧の並び順</th>
          <td>
            <select name="order">
            <?php
            for ($i = 1; $i <= 20; $i++) {
              echo '<option value="'.$i.'">'.$i.'番目</option>';
            }
            ;?>
            </select>
          </td>
        </tr>
        <tr>
          <th>表示 / 非表示</th>
          <td>
            <select name="active">
              <option value="1">表示</option>
              <option value="2">非表示</option>
            </select>
          </td>
        </tr>
        <tr>
          <th>公開日時</th>
          <td><input type="text" name="pub_date">　※「2017.06.15」という形式で入力してください。</td>
        </tr>
        <tr>
          <th>対象コース</th>
          <td>
            <select name="target_course">
            <?php
              foreach($available_courses as $course_key => $course_name) {
                echo '<option value="' . $course_key . '">' . $course_name . '</option>';
              }
            ?>
            </select>
          </td>
        </tr>
        <tr>
          <th>タグ</th>
          <td>
            <div class="checkbox-group">
            <?php
              if(!empty($tag_list)) {
                foreach($tag_list as $tag) {
                  echo '<div class="checkbox-item">';
                  echo '<input type="checkbox" name="tags[]" value="' . $tag['tag_id'] . '" id="tag_' . $tag['tag_id'] . '">';
                  echo '<label for="tag_' . $tag['tag_id'] . '">' . htmlspecialchars($tag['tag_name']) . '</label>';
                  echo '</div>';
                }
              } else {
                echo '<p>登録されたタグがありません。<a href="register-tag.php">タグを登録</a></p>';
              }
            ?>
            </div>
          </td>
        </tr>
      </table>
      <p><input type="submit" id="btnRegisterBottom" class="Btn" value="登録" name="register"></p>
    </form>
  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->

</body>
</html>