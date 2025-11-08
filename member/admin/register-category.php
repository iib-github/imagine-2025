<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/CategoryModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('admin') === false) {
    header("Location: login.php");
    exit;
  }

  // カテゴリー登録時
  if($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_model = new CategoryModel();

    // 登録情報
    $category_data = array(
      'category_number' => $_POST['number'],
      'category_title' => $_POST['title'],
      'content_text' => $_POST['content'],
      'indicate_flag' => $_POST['indicate_flag'],
      'pub_date' => $_POST['pub_date'],
      'target_course' => isset($_POST['target_course']) ? $_POST['target_course'] : 'all',
    );

    $success = $category_model->registerCategory($category_data);
    if($success) {
      header("Location: list-category.php?status=created");
      exit;
    }


    // TODO 必須チェックとメアド重複チェック、及びエラー時のメッセージ

  }

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>カテゴリー登録 | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />

</head>

<body>

<!-- Wrapper starts -->
<div id="wrapper">
  <div class="InBox"><!-- INBOX -->
  <h1>カテゴリー登録</h1>

    <?php
    $menu_active = 'ctgr';
    include_once 'menu.php';
    ?>

    <form method="POST" action="register-category.php" enctype="multipart/form-data">
      <p><input type="submit" id="btnRegister" class="Btn" value="登録" name="register"></p>
      <table class="member">
        <tr>
          <th>ナンバー（Lesson〇〇）</th>
          <td>
            <select name="number">
            <?php
            for ($i = 0; $i <= 10; $i++) {
              echo '<option value="'.$i.'">Lesson'.$i.'</option>';
            }
            ;?>
            <option value="11">イマジンラジオ</option>
            <option value="12">QAライブ動画</option>
            </select>
          </td>
        </tr>
        <tr>
          <th>タイトル</th>
          <td><input type="text" name="title" style="width:800px;"></td>
        </tr>
        <tr>
          <th>TOPバナー画像</th>
          <td>
            <input type="file" name="bnr-img" id="bnr-img">
          </td>
        </tr>
        <tr>
          <th>詳細ページ画像</th>
          <td>
            <input type="file" name="main-img" id="main-img">
          </td>
        </tr>
        <tr>
          <th>説明テキスト</th>
          <td><textarea name="content" id="content" style="width:800px;height:100px;"></textarea></td>
        </tr>
        <tr>
          <th>コンテンツ数</th>
          <td>コンテンツ登録後に自動集計されます。</td>
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
          <th>対象コース</th>
          <td>
            <select name="target_course">
              <option value="basic">ベーシック</option>
              <option value="advance" selected="selected">アドバンス</option>
            </select>
          </td>
        </tr>
        <tr>
          <th>公開日時</th>
          <td><input type="date" name="pub_date">　※カレンダーから日付を選択してください。</td>
        </tr>
      </table>
    </form>
  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->

</body>
</html>