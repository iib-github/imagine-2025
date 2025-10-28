<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/ContentModel.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/CategoryModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('admin') === false) {
    header("Location: login.php");
    exit;
  }

  // 全てのカテゴリー取得
  $category_model = new CategoryModel();
  $category_list = $category_model->select(null, array('category_number' => BaseModel::ORDER_ASC));

  // コンテンツ登録時
  if($_SERVER["REQUEST_METHOD"] == "POST") {
    $content_model = new ContentModel();

    // 登録情報
    $content_data = array(
      'category_id' => $_POST['category'],
      'content_week' => $_POST['week'],
      'content_title' => $_POST['title'],
      'content_movie_url' => $_POST['movie_url'],
      'content_text' => $_POST['discription'],
      'display_order' => $_POST['order'],
      'indicate_flag' => $_POST['active'],
      'pub_date' => $_POST['pub_date'],
    );
    if(isset($_POST['is_faq'])) {
      $content_data['is_faq'] = $_POST['is_faq'];
    } else {
      $content_data['is_faq'] = $content_model::IS_NOT_FAQ;
    }

    $success = $content_model->registerContent($content_data);
    if($success) {
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
          <th>動画埋め込みコード</th>
          <td><textarea name="movie_url" style="width:800px;height:100px;"></textarea></td>
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
              echo '<option value="'.$i.'"'.$selected.'>'.$i.'番目</option>';
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
      </table>
    </form>
  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->

</body>
</html>