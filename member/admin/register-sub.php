<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
require_once dirname(__FILE__) . '/../scripts/model/SubModel.class.php';
require_once dirname(__FILE__) . '/../scripts/model/CategoryModel.class.php';
require_once dirname(__FILE__) . '/../scripts/model/ContentModel.class.php';
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
    $sub_model = new SubModel();

    // 登録情報
    $sub_data = array(
      'category_id' => $_POST['category_id'],
      'content_title' => $_POST['content_title'],
      'content_text' => $_POST['content_text'],
      'display_order' => $_POST['display_order'],
      'indicate_flag' => $_POST['indicate_flag'],
      'pub_date' => $_POST['pub_date'],
      'target_course' => isset($_POST['target_course']) ? $_POST['target_course'] : ContentModel::TARGET_COURSE_ADVANCE,
    );

    $success = $sub_model->registerSub($sub_data);
    if($success) {
      header("Location: list-sub.php?status=created");
      exit;
    }

  }

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>資料登録 | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />
</head>

<body>

<!-- Wrapper starts -->
<div id="wrapper">
  <div class="InBox"><!-- INBOX -->
    <h1>資料登録</h1>

<?php
  $menu_active = 'sub';
  include_once 'menu.php';
?>

    <form method="POST" action="register-sub.php" enctype="multipart/form-data">
      <p><input type="submit" id="btnRegister" class="Btn" value="登録" name="register"></p>
      <table class="member">
        <tr>
          <th>対象コース</th>
          <td>
            <select name="target_course">
              <option value="<?php echo ContentModel::TARGET_COURSE_ADVANCE; ?>">アドバンス（全体）</option>
              <option value="<?php echo ContentModel::TARGET_COURSE_BASIC; ?>">ベーシック</option>
            </select>
          </td>
        </tr>
        <tr>
          <th>紐づくカテゴリー</th>
          <td>
            <select name="category_id">
            <?php
              foreach ($category_list as $category) {
                echo '<option value="' . $category['category_id'] . '" >Lesson' . $category['category_number'] . '</option>';
              }
            ?>
            </select>
          </td>
        </tr>
        <tr>
        <th>コンテンツタイトル</th>
          <td><input type="text" name="content_title" style="width:800px;"></td>
        </tr>
        <tr>
          <th>サムネイル画像</th>
          <td>
            <input type="file" name="thumbnail" id="thumbnail">
          </td>
        <tr>
          <th>資料アップロード</th>
          <td><input type="file" name="content" id="content"></td>
        </tr>
        <tr>
          <th>説明テキスト</th>
          <td><textarea name="content_text" id="colume" style="width:800px;height:100px;"></textarea></td>
        </tr>
        <tr>
          <th>一覧の並び順</th>
          <td>
            <select name="display_order">
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
            <select name="indicate_flag">
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