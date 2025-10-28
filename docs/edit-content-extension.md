# コンテンツ編集画面の拡張ガイド

## 概要
既存の`edit-content.php`にタグ編集、複数動画編集、コース選択機能を追加するための実装ガイドです。

## 追加する機能

### 1. 必要なRequireの追加

編集画面の冒頭のrequire文に以下を追加：

```php
require_once dirname(__FILE__) . '/../scripts/model/TagModel.class.php';
require_once dirname(__FILE__) . '/../scripts/model/ContentVideoModel.class.php';
```

### 2. PHP処理の拡張

#### POST処理の拡張

既存のPOST処理に以下を追加：

```php
// コース情報を追加
$data['target_course'] = isset($_POST['target_course']) ? $_POST['target_course'] : ContentModel::TARGET_COURSE_ALL;

// コンテンツ更新後、タグを更新
if(!empty($_POST['tags'])) {
  $tag_ids = array_map('intval', $_POST['tags']);
  $content_model->setContentTags($content_id, $tag_ids);
} else {
  // タグをクリア
  $content_model->setContentTags($content_id, array());
}

// 動画を更新
$video_model = new ContentVideoModel();

// 既存の動画を削除
$video_model->deleteVideosByContentId($content_id);

// 新しい動画を登録
if(!empty($_POST['video_urls'])) {
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
```

#### GET処理の拡張

GET時にタグと動画を取得：

```php
// GET時にコンテンツ取得後に追加
$tag_model = new TagModel();
$content_tags = $tag_model->getTagList();  // 全タグ取得
$selected_tags = $content_model->getContentTags($content_id);  // 選択済みタグ取得
$selected_tag_ids = array_column($selected_tags, 'tag_id');

$video_model = new ContentVideoModel();
$content_videos = $video_model->getVideosByContentId($content_id);  // 関連動画取得

$available_courses = $content_model->getAvailableCourses();
```

### 3. HTML フォーム要素の追加

#### 動画編集セクション

「動画埋め込みコード」の行を以下に置き換え：

```html
<tr>
  <th>動画</th>
  <td>
    <button type="button" class="add-video-btn" onclick="addVideoField()">＋ 動画を追加</button>
    <div id="video-container">
    <?php
      if(!empty($content_videos)) {
        foreach($content_videos as $index => $video) {
          echo '<div class="video-entry">';
          echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">';
          echo '<strong>動画 ' . ($index + 1) . '</strong>';
          echo '<button type="button" class="remove-video-btn" onclick="removeVideoField(this)">削除</button>';
          echo '</div>';
          echo '<input type="text" name="video_titles[]" placeholder="動画タイトル" value="' . htmlspecialchars($video['video_title']) . '">';
          echo '<textarea name="video_urls[]" placeholder="動画埋め込みコード">' . htmlspecialchars($video['video_url']) . '</textarea>';
          echo '<input type="text" name="thumbnail_urls[]" placeholder="サムネイル画像URL" value="' . htmlspecialchars($video['thumbnail_url']) . '">';
          echo '</div>';
        }
      }
    ?>
    </div>
  </td>
</tr>
```

#### コース選択セクション

公開日時の行の後に追加：

```html
<tr>
  <th>対象コース</th>
  <td>
    <select name="target_course">
    <?php
      foreach($available_courses as $course_key => $course_name) {
        $selected = ($content['target_course'] === $course_key) ? ' selected="selected"' : '';
        echo '<option value="' . $course_key . '"' . $selected . '>' . $course_name . '</option>';
      }
    ?>
    </select>
  </td>
</tr>
```

#### タグ選択セクション

コース選択の後に追加：

```html
<tr>
  <th>タグ</th>
  <td>
    <div class="checkbox-group">
    <?php
      if(!empty($content_tags)) {
        foreach($content_tags as $tag) {
          $checked = in_array($tag['tag_id'], $selected_tag_ids) ? ' checked="checked"' : '';
          echo '<div class="checkbox-item">';
          echo '<input type="checkbox" name="tags[]" value="' . $tag['tag_id'] . '"' . $checked . ' id="tag_' . $tag['tag_id'] . '">';
          echo '<label for="tag_' . $tag['tag_id'] . '">' . htmlspecialchars($tag['tag_name']) . '</label>';
          echo '</div>';
        }
      } else {
        echo '<p>登録されたタグがありません。</p>';
      }
    ?>
    </div>
  </td>
</tr>
```

### 4. スタイルとJavaScriptの追加

`<head>`セクションに以下を追加：

```html
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
      <input type="text" name="video_titles[]" placeholder="動画タイトル">
      <textarea name="video_urls[]" placeholder="動画埋め込みコード（iframe等）" style="height: 80px;"></textarea>
      <input type="text" name="thumbnail_urls[]" placeholder="サムネイル画像URL">
    `;
    
    videoContainer.appendChild(newEntry);
  }
  
  function removeVideoField(btn) {
    btn.closest('.video-entry').remove();
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
</script>
```

## 実装手順

1. `edit-content.php`ファイルを開く
2. 上記のrequire文を追加
3. POST処理を拡張
4. GET処理を拡張
5. HTML フォーム要素を追加
6. スタイルとJavaScriptを追加
7. テストして動作確認

## 注意事項

- 動画の再登録時に既存データを全削除してから新規作成するため、削除漏れがない
- タグの更新も同様に、既存のタグを一度クリアしてから新規追加
- コース選択フィールドのデフォルト値は「全コース」
- バリデーションは登録画面と同様に実装することを推奨
