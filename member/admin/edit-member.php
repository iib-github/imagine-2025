<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/MemberModel.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/CategoryModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('admin') === false) {
    header("Location: login.php");
    exit;
  }

  if($_SERVER["REQUEST_METHOD"] == "GET") {

    if($_GET['mid']) {
      // 編集対象の会員情報取得
      $member_model = new MemberModel();
      $member = $member_model->select(array('member_id'=>$_GET['mid']));
      $member = $member[0];

      // DBからメンバーが取れなければ一覧画面に飛ばす。
      if(empty($member)) {
        header("Location: list-member.php");
        exit;
      }

      // 達成率表示のため全課題（カテゴリー）を取得
      $category_model = new CategoryModel();
      $category_list = $category_model->select(null, array('category_number'=>$category_model::ORDER_ASC));

      // 課題とそれ対する達成率が対になった配列を生成。
      $score_list = array();
      foreach ($category_list as $ctg) {
        $score = $member_model->getScore($_GET['mid'], $ctg['category_id']);
        $score_list[$ctg['category_number']] = $score;
      }

    } else {
      // GETパラメーターからメンバーIDが取れなければ一覧画面に飛ばす。
      header("Location: list-member.php");
      exit;
    }
  } else { // POST時
    // 入力情報で会員を更新
    $data = array(
      'member_name' => $_POST['name'],
      'select_course' => $_POST['course'],
      'login_mail' => $_POST['mail'],
      'login_password' => $_POST['password'],
    );
    $member_model = new MemberModel();
    $member_model->update($data, array('member_id'=>$_POST['member_id']));
    header("Location: list-member.php");
  }

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>会員編集 | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />
</head>

<body>

<!-- Wrapper starts -->
<div id="wrapper">
  <div class="InBox"><!-- INBOX -->
    <h1>会員詳細</h1>

<?php
  $menu_active = 'cstm';
  include_once 'menu.php';
?>

    <form method="POST" action="edit-member.php">
      <p><input type="submit" id="btnUpdate" class="Btn" value="更新" name="update"></p>
      <input type="hidden" name="member_id" value="<?php echo $member["member_id"]; ?>">
      <table class="member">
        <tr>
          <th style="width:150px">会員ID</th>
          <td><?php echo $member['member_id']; ?></td>
        </tr>
        <tr>
          <th>名前</th>
          <td><input type="text" name="name" style="width:800px;" value="<?php echo htmlspecialchars($member["member_name"], ENT_QUOTES, 'UTF-8'); ?>"></td>
        </tr>
        <tr>
          <th>コース</th>
          <td>
            <select name="course">
              <option value="1"<?php if($member['select_course'] == 1) echo ' selected="selected"';?>>アドバンス</option>
              <option value="2"<?php if($member['select_course'] == 2) echo ' selected="selected"';?>>ベーシック</option>
              <option value="3"<?php if($member['select_course'] == 3) echo ' selected="selected"';?>>その他</option>
            </select>
            </td>
        </tr>
        <tr>
          <th>メールアドレス</th>
          <td><input type="text" name="mail" style="width:300px;" value="<?php echo htmlspecialchars($member["login_mail"], ENT_QUOTES, 'UTF-8'); ?>"></td>
        </tr>
        <tr>
          <th>パスワード</th>
          <td><input type="text" name="password" style="width:300px;" value="<?php echo htmlspecialchars(
          $member["login_password"]); ?>"></td>
        </tr>

        <?php if(isset($score_list)) : ?>
        <?php foreach ($score_list as $number => $score) : ?>
        <tr>
          <th>Lesson<?php echo $number; ?>の達成度</th>
          <td><?php echo $score; ?> %</td>
        </tr>
        <?php endforeach;?>
        <?php endif;?>

      </table>
    </form>
  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->

</body>
</html>