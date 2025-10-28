<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/CategoryModel.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/SubModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('admin') === false) {
    header("Location: login.php");
    exit;
  }

  // 全てのカテゴリー取得
  $category_model = new CategoryModel();
  $category_list = $category_model->select(null, array('category_number' => BaseModel::ORDER_ASC));

  $sub_model = new SubModel();

  if($_SERVER["REQUEST_METHOD"] == "GET") {
    if($_GET['sub_id']) {
      // 編集対象のコンテンツ情報取得
      $sub = $sub_model->select(array('sub_id'=>$_GET['sub_id']));
      $sub = $sub[0];

      // DBからコンテンツが取れなければ一覧画面に飛ばす。
      if(empty($sub)) {
        header("Location: list-sub.php");
        exit;
      }
    } else {
      // GETパラメーターからメンバーIDが取れなければ一覧画面に飛ばす。
      header("Location: list-sub.php");
      exit;
    }
  } else { // POST時
    // 入力情報でコンテンツを更新
    $data = array(
      'sub_id' => $_POST['sub_id'],
      'category_id' => $_POST['category_id'],
      'content_title' => $_POST['content_title'],
      'content_text' => $_POST['content_text'],
      'display_order' => $_POST['display_order'],
      'indicate_flag' => $_POST['indicate_flag'],
      'pub_date' => $_POST['pub_date'],
    );
    $sub_model->registerSub($data);
    header("Location: list-sub.php");
    exit;
  }

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>資料編集 | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />
</head>

<body>
<!-- Wrapper starts -->
<div id="wrapper">
  <div class="InBox"><!-- INBOX -->
    <h1>資料詳細</h1>

<?php
  $menu_active = 'sub';
  include_once 'menu.php';
?>

    <form method="POST" action="edit-sub.php" enctype="multipart/form-data">
      <p><input type="submit" id="btnUpdate" class="Btn" value="更新" name="update"></p>
      <input type="hidden" name="sub_id" value="<?php echo $sub["sub_id"]; ?>">
      <table class="member">
        <tr>
          <th style="width:150px">コンテンツID</th>
          <td><?php echo $sub['sub_id']; ?></td>
        </tr>
        <tr>
          <th>紐づくカテゴリー</th>
          <td>
            <select name="category_id">
            <?php
              foreach ($category_list as $category) {
                if($category['category_id'] == $sub['category_id']) {
                  $selected = ' selected="selected"';
                } else {
                  $selected = '';
                }
                echo '<option value="'.$category['category_id'].'"'.$selected.'>Lesson'.$category['category_number'] . '</option>';
              }
            ?>
            </select>
          </td>
        </tr>
        <tr>
          <th>コンテンツタイトル</th>
          <td><input type="text" name="content_title" style="width:800px;" value="<?php echo htmlspecialchars($sub["content_title"], ENT_QUOTES, 'UTF-8'); ?>"></td>
        </tr>
        <tr>
          <th>サムネイル画像</th>
          <td>
            <?php if(!empty($sub["thumbnail_url"])){ ?>
            https://the-imagine.com/membership/member/<?php echo $sub["thumbnail_url"]; ?><br>
            <img src="<?php echo '../'.$sub["thumbnail_url"].'?='.time(); ?>"><br>
            <?php } ?>
            <input type="file" name="thumbnail" id="thumbnail">
          </td>
        </tr>
        <tr>
          <th>資料アップロード</th>
          <td>
            <?php if(!empty($sub["content_url"])){ ?>
            <a href="/membership/member/<?php echo $sub["content_url"]; ?>" target="_blank">https://the-imagine.com/membership/member/<?php echo $sub["content_url"]; ?></a><br>
            <?php } ?>
            <input type="file" name="content" id="content">
          </td>
        </tr>
        <tr>
          <th>説明テキスト</th>
          <td><textarea name="content_text" id="colume" style="width:800px;height:100px;"><?php echo htmlspecialchars($sub["content_text"], ENT_QUOTES, 'UTF-8'); ?></textarea></td>
        </tr>
        <tr>
          <th>一覧の並び順</th>
          <td>
            <select name="display_order">
            <?php
            for ($i = 1; $i <= 20; $i++) {
              if($i == $sub['display_order']) {
                $selected = ' selected="selected"';
              } else {
                $selected = '';
              }
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
              <option value="1"<?php if($sub['indicate_flag'] == 1) echo ' selected="selected"';?>>表示</option>
              <option value="2"<?php if($sub['indicate_flag'] == 2) echo ' selected="selected"';?>>非表示</option>
            </select>
          </td>
        </tr>
        <tr>
          <th>公開日時</th>
          <td><input type="text" name="pub_date" value="<?php echo htmlspecialchars($sub["pub_date"], ENT_QUOTES, 'UTF-8'); ?>">　※「2017.06.15」という形式で入力してください。</td>
        </tr>
      </table>
    </form>
  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->

</body>
</html>