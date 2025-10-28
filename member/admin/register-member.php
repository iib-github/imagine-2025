<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/../scripts/util.php';
  require_once dirname(__FILE__) . '/../scripts/model/MemberModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('admin') === false) {
    header("Location: login.php");
    exit;
  }

  // 会員登録時
  if($_SERVER["REQUEST_METHOD"] == "POST") {
    $member_model = new MemberModel();

    // 登録情報
    $member_data = array(
      'member_name' => $_POST['name'],
      'select_course' => $_POST['course'],
      'login_mail' => $_POST['mail'],
      'login_password' => makeRandStr(8),
    );

    // TODO 必須チェックとメアド重複チェック、及びエラー時のメッセージ

    $success = $member_model->insert($member_data);
    if($success) {
      header("Location: list-member.php");
      exit;
    }

  }

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>会員登録 | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />

</head>

<body>
<!-- Wrapper starts -->
<div id="wrapper">
  <div class="InBox"><!-- INBOX -->
    <h1>会員登録</h1>

<?php
  $menu_active = 'cstm';
  include_once 'menu.php';
?>

    <form method="POST" action="register-member.php">
      <p><input type="submit" id="btnRegister" class="Btn" value="登録" name="register"></p>

      <table class="member">
        <tr>
          <th>名前</th>
          <td><input type="text" name="name" style="width:800px;"></td>
        </tr>
        <tr>
          <th>コース</th>
          <td>
            <select name="course">
              <option value="1" >プレミアムコース</option>
              <option value="2" >ベーシックコース</option>
            </select>
          </td>
        </tr>
        <tr>
          <th>メールアドレス</th>
          <td><input type="text" name="mail" style="width:300px;"></td>
        </tr>
<!--         <tr>
          <th>パスワード</th>
          <td><input type="text" name="password" style="width:300px;"></td>
        </tr> -->
      </table>
    </form>
  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->

</body>
</html>