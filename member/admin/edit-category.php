<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/CategoryModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('admin') === false) {
    header("Location: login.php");
    exit;
  }

  if($_SERVER["REQUEST_METHOD"] == "GET") {

    if($_GET['ctg_id']) {
      // 編集対象の課題（カテゴリー）情報取得
      $category_model = new CategoryModel();
      $category = $category_model->select(array('category_id'=>$_GET['ctg_id']));
      $category = $category[0];

      // DBからカテゴリーが取れなければ一覧画面に飛ばす。
      if(empty($category)) {
        header("Location: list-category.php");
        exit;
      }
    } else {
      // GETパラメーターからメンバーIDが取れなければ一覧画面に飛ばす。
      header("Location: list-category.php");
      exit;
    }
  } else { // POST時
    // 入力情報でカテゴリーを更新
    $data = array(
      'category_id' => $_POST['category_id'],
      'category_number' => $_POST['number'],
      'category_title' => $_POST['title'],
      'content_text' => $_POST['discription'],
      'number_of_contents' => $_POST['number_of_contents'],
      'indicate_flag' => $_POST['indicate_flag'],
      'pub_date' => $_POST['pub_date'],
    );
    $category_model = new CategoryModel();
    $category_model->registerCategory($data);
    header("Location: list-category.php");
    exit;
  }

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>カテゴリー編集 | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />

</head>

<body>

<!-- Wrapper starts -->
<div id="wrapper">
  <div class="InBox"><!-- INBOX -->
    <h1>カテゴリー詳細</h1>

    <?php
    $menu_active = 'ctgr';
    include_once 'menu.php';
    ?>

    <form method="POST" action="edit-category.php" enctype="multipart/form-data">
      <p><input type="submit" id="btnUpdate" class="Btn" value="更新" name="update"></p>
      <input type="hidden" name="category_id" value="<?php echo $category["category_id"]; ?>">
      <table class="member">
        <tr>
          <th style="width:150px">カテゴリーID</th>
          <td><?php echo $category['category_id']; ?></td>
        </tr>
        <tr>
          <th>ナンバー（Lesson〇〇）</th>
          <td>
            <select name="number">
            <?php
            for ($i = 0; $i <= 10; $i++) {
              if($i == $category['category_number']) {
                $selected = ' selected="selected"';
              } else {
                $selected = '';
              }
              echo '<option value="'.$i.'"'.$selected.'>Lesson'.$i.'</option>';
            }
            ;?>
            <option value="11"<?php if ('11' == $category['category_number']){echo ' selected="selected"';} ?>>QAライブ動画</option>
            <option value="12"<?php if ('12' == $category['category_number']){echo ' selected="selected"';} ?>>イマジンラジオ</option>
            </select>
          </td>
        </tr>
        <tr>
          <th>タイトル</th>
          <td><input type="text" name="title" style="width:800px;" value="<?php echo htmlspecialchars($category["category_title"], ENT_QUOTES, 'UTF-8'); ?>"></td>
        </tr>
        <tr>
          <th>TOPバナー画像</th>
          <td>
            <?php if(!empty($category["category_top_img"])){ ?>
            https://the-imagine.com/membership/member/<?php echo $category["category_top_img"]; ?><br>
            <img src="<?php echo '../'.$category["category_top_img"].'?='.time(); ?>"><br>
            <?php } ?>
            <input type="file" name="bnr-img" id="bnr-img">
          </td>
        </tr>
        <tr>
          <th>詳細ページ画像</th>
          <td>
            <?php if(!empty($category["category_list_img"])){ ?>
            https://the-imagine.com/membership/member/<?php echo $category["category_list_img"]; ?><br>
            <img src="<?php echo '../'.$category["category_list_img"].'?='.time(); ?>"><br>
            <?php } ?>
            <input type="file" name="main-img" id="main-img">
          </td>
        </tr>
        <tr>
          <th>説明テキスト</th>
          <td><textarea name="discription" id="discription" style="width:800px;height:100px;"><?php echo htmlspecialchars($category["content_text"], ENT_QUOTES, 'UTF-8'); ?></textarea></td>
        </tr>
        <tr>
          <th>コンテンツ数</th>
          <td>
            <select name="number_of_contents">
            <?php
            for ($i = 1; $i <= 20; $i++) {
              if($i == $category['number_of_contents']) {
                $selected = ' selected="selected"';
              } else {
                $selected = '';
              }
              echo '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
            }
            ;?>
            </select>
          </td>
        </tr>
        <tr>
          <th>表示 / 非表示</th>
          <td>
            <select name="indicate_flag">
              <option value="1"<?php if($category['indicate_flag'] == 1) echo ' selected="selected"';?>>表示</option>
              <option value="2"<?php if($category['indicate_flag'] == 2) echo ' selected="selected"';?>>非表示</option>
            </select>
          </td>
        </tr>
        <tr>
          <th>公開日時</th>
          <td><input type="text" name="pub_date" value="<?php echo htmlspecialchars($category["pub_date"], ENT_QUOTES, 'UTF-8'); ?>">　※「2017.06.15」という形式で入力してください。</td>
        </tr>
      </table>
    </form>
  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->

</body>
</html>