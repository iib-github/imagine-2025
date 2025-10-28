<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
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

  $content_model = new ContentModel();

  if($_SERVER["REQUEST_METHOD"] == "GET") {
    if($_GET['cont_id']) {
      // 編集対象のコンテンツ情報取得
      $content = $content_model->select(array('content_id'=>$_GET['cont_id']));
      $content = $content[0];

      // DBからコンテンツが取れなければ一覧画面に飛ばす。
      if(empty($content)) {
        header("Location: list-content.php");
        exit;
      }
    } else {
      // GETパラメーターからメンバーIDが取れなければ一覧画面に飛ばす。
      header("Location: list-content.php");
      exit;
    }
  } else { // POST時
    // 入力情報でコンテンツを更新
    $data = array(
      'content_id' => $_POST['content_id'],
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
      $data['is_faq'] = $_POST['is_faq'];
    } else {
      $data['is_faq'] = $content_model::IS_NOT_FAQ;
    }
    $content_model->registerContent($data);
    header("Location: list-content.php");
    exit;
  }

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>コンテンツ編集 | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />
</head>

<body>
<!-- Wrapper starts -->
<div id="wrapper">
  <div class="InBox"><!-- INBOX -->
    <h1>コンテンツ詳細</h1>

<?php
  $menu_active = 'cnts';
  include_once 'menu.php';
?>

    <form method="POST" action="edit-content.php" enctype="multipart/form-data">
      <p><input type="submit" id="btnUpdate" class="Btn" value="更新" name="update"></p>
      <input type="hidden" name="content_id" value="<?php echo $content["content_id"]; ?>">
      <table class="member">
        <tr>
          <th style="width:150px">コンテンツID</th>
          <td><?php echo $content['content_id']; ?></td>
        </tr>
        <tr>
          <th>紐づくカテゴリー</th>
          <td>
            <select name="category">
            <?php
              foreach ($category_list as $category) {
                if($category['category_id'] == $content['category_id']) {
                  $selected = ' selected="selected"';
                } else {
                  $selected = '';
                }
                if ($category['category_number'] == '12') {
                  echo '<option value="' . $category['category_id'] . '"'.$selected.'>イマジンラジオ</option>';
                } elseif ($category['category_number'] == '11') {
                  echo '<option value="' . $category['category_id'] . '"'.$selected.'>QAライブ動画</option>';
                } else {
                  echo '<option value="'.$category['category_id'].'"'.$selected.'>Lesson'.$category['category_number'] . '</option>';
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
            if($i == $content['content_week']) {
              $selected = ' selected="selected"';
            } else {
              $selected = '';
            }
            echo '<option value="'.$i.'"'.$selected.'>'.$i.'週目</option>';
          }
          ;?>
          </select>
        </tr>
        <tr>
          <th>コンテンツタイトル</th>
          <td><input type="text" name="title" style="width:800px;" value="<?php echo htmlspecialchars($content["content_title"], ENT_QUOTES, 'UTF-8'); ?>"></td>
        </tr>
        <tr>
          <th>サムネイル画像</th>
          <td>
            <?php if(!empty($content["thumbnail_url"])){ ?>
            https://the-imagine.com/membership/member/<?php echo $content["thumbnail_url"]; ?><br>
            <img src="<?php echo '../'.$content["thumbnail_url"].'?='.time(); ?>"><br>
            <?php } ?>
            <input type="file" name="thumbnail" id="thumbnail">
          </td>
        </tr>
        <tr>
          <th>動画埋め込みコード</th>
          <td><textarea name="movie_url" style="width:800px;height:100px;"><?php echo htmlspecialchars($content["content_movie_url"], ENT_QUOTES, 'UTF-8'); ?></textarea></td>
        </tr>
        <tr>
          <th>説明テキスト</th>
          <td><textarea name="discription" id="colume" style="width:800px;height:100px;"><?php echo htmlspecialchars($content["content_text"], ENT_QUOTES, 'UTF-8'); ?></textarea></td>
        </tr>
        <tr>
          <th>講座資料ダウンロード</th>
          <td>
            <?php if(!empty($content["text_dl_url"])){ ?>
            <a href="/membership/member/<?php echo $content["text_dl_url"]; ?>" target="_blank">https://the-imagine.com/membership/member/<?php echo $content["text_dl_url"]; ?></a>
            <?php } ?>
            <br><input type="file" name="txt_url" id="txt_url">
          </td>
        </tr>
        <tr>
          <th>文字起こし資料の<br>ダウンロード</th>
          <td>
            <?php if(!empty($content["message_dl_url"])){ ?>
            <a href="/membership/member/<?php echo $content["message_dl_url"]; ?>" target="_blank">https://the-imagine.com/membership/member/<?php echo $content["message_dl_url"]; ?></a>
            <?php } ?>
            <br><input type="file" name="document" id="document">
          </td>
        </tr>
        <tr>
          <th>一覧の並び順</th>
          <td>
            <select name="order">
            <?php
            for ($i = 1; $i <= 20; $i++) {
              if($i == $content['display_order']) {
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
            <select name="active">
              <option value="1"<?php if($content['indicate_flag'] == 1) echo ' selected="selected"';?>>表示</option>
              <option value="2"<?php if($content['indicate_flag'] == 2) echo ' selected="selected"';?>>非表示</option>
            </select>
          </td>
        </tr>
        <tr>
          <th>公開日時</th>
          <td><input type="text" name="pub_date" value="<?php echo htmlspecialchars($content["pub_date"], ENT_QUOTES, 'UTF-8'); ?>">　※「2017.06.15」という形式で入力してください。</td>
        </tr>
      </table>
    </form>
  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->

</body>
</html>