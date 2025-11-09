<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/CategoryModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('admin') === false) {
    header("Location: login.php");
    exit;
  }

  $errors = array();
  $input_values = array(
    'number' => '0',
    'title' => '',
    'content' => '',
    'indicate_flag' => '1',
    'target_course' => 'advance',
    'use_week_flag' => '1',
    'pub_date' => ''
  );

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
      'use_week_flag' => isset($_POST['use_week_flag']) ? (int)$_POST['use_week_flag'] : 1,
    );

    $input_values['number'] = isset($_POST['number']) ? $_POST['number'] : $input_values['number'];
    $input_values['title'] = isset($_POST['title']) ? $_POST['title'] : $input_values['title'];
    $input_values['content'] = isset($_POST['content']) ? $_POST['content'] : $input_values['content'];
    $input_values['indicate_flag'] = isset($_POST['indicate_flag']) ? $_POST['indicate_flag'] : $input_values['indicate_flag'];
    $input_values['target_course'] = isset($_POST['target_course']) ? $_POST['target_course'] : $input_values['target_course'];
    $input_values['use_week_flag'] = isset($_POST['use_week_flag']) ? $_POST['use_week_flag'] : $input_values['use_week_flag'];
    $input_values['pub_date'] = isset($_POST['pub_date']) ? $_POST['pub_date'] : $input_values['pub_date'];

    $success = $category_model->registerCategory($category_data);
    if($success) {
      header("Location: list-category.php?status=created");
      exit;
    }

    $error_message = $category_model->getLastErrorMessage();
    if (!empty($error_message)) {
      $errors[] = $error_message;
    } else {
      $errors[] = 'カテゴリーの登録に失敗しました。入力内容をご確認ください。';
    }
  } else {
    // 初回表示時：フォーム初期値を設定
    $input_values['target_course'] = 'advance';
    $input_values['indicate_flag'] = '1';
    $input_values['use_week_flag'] = '1';
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
      <?php if (!empty($errors)): ?>
      <div class="error-list">
        <ul>
        <?php foreach ($errors as $error): ?>
          <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
        <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <table class="member">
        <tr>
          <th>ナンバー（Lesson〇〇）</th>
          <td>
            <select name="number">
            <?php
            for ($i = 0; $i <= 10; $i++) {
              $selected = ((string)$input_values['number'] === (string)$i) ? ' selected="selected"' : '';
              echo '<option value="'.$i.'"'.$selected.'>Lesson'.$i.'</option>';
            }
            ;?>
            <option value="11"<?php if ((string)$input_values['number'] === '11') echo ' selected="selected"'; ?>>イマジンラジオ</option>
            <option value="12"<?php if ((string)$input_values['number'] === '12') echo ' selected="selected"'; ?>>QAライブ動画</option>
            </select>
          </td>
        </tr>
        <tr>
          <th>タイトル</th>
          <td><input type="text" name="title" style="width:800px;" value="<?php echo htmlspecialchars($input_values['title'], ENT_QUOTES, 'UTF-8'); ?>"></td>
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
          <td><textarea name="content" id="content" style="width:800px;height:100px;"><?php echo htmlspecialchars($input_values['content'], ENT_QUOTES, 'UTF-8'); ?></textarea></td>
        </tr>
        <tr>
          <th>コンテンツ数</th>
          <td>コンテンツ登録後に自動集計されます。</td>
        </tr>
        <tr>
          <th>表示 / 非表示</th>
          <td>
            <select name="indicate_flag">
              <option value="1"<?php if ($input_values['indicate_flag'] == '1') echo ' selected="selected"'; ?>>表示</option>
              <option value="2"<?php if ($input_values['indicate_flag'] == '2') echo ' selected="selected"'; ?>>非表示</option>
            </select>
          </td>
        </tr>
        <tr>
          <th>対象コース</th>
          <td>
            <select name="target_course">
              <option value="basic"<?php if ($input_values['target_course'] === 'basic') echo ' selected="selected"'; ?>>ベーシック</option>
              <option value="advance"<?php if ($input_values['target_course'] === 'advance') echo ' selected="selected"'; ?>>アドバンス</option>
            </select>
          </td>
        </tr>
        <tr>
          <th>Weekの利用</th>
          <td>
            <select name="use_week_flag">
              <option value="1"<?php if ((string)$input_values['use_week_flag'] === '1') echo ' selected="selected"'; ?>>Weekを表示する</option>
              <option value="0"<?php if ((string)$input_values['use_week_flag'] === '0') echo ' selected="selected"'; ?>>Weekを表示しない</option>
            </select>
            <p style="margin:8px 0 0;font-size:12px;color:#555;">※「表示しない」を選択すると、会員画面・管理画面の該当カテゴリでWeek項目が非表示になります。</p>
          </td>
        </tr>
        <tr>
          <th>公開日時</th>
          <td><input type="date" name="pub_date" value="<?php echo htmlspecialchars($input_values['pub_date'], ENT_QUOTES, 'UTF-8'); ?>">　※カレンダーから日付を選択してください。</td>
        </tr>
      </table>
    </form>
  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->

</body>
</html>