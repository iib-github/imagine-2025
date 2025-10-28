<?php
  // MEMO 20251028：このファイルを使ってユーザー登録を実施する
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/../scripts/util.php';
  require_once dirname(__FILE__) . '/../scripts/model/MemberModel.class.php';

  // 会員登録時
  if($_SERVER["REQUEST_METHOD"] == "POST") {
    $member_model = new MemberModel();

    // if (isset($_POST['free50'])) {
    //   $course = $_POST['free50'];
    // } else {
    //   $course = '';
    // }

    // if ($course == "マスターコース") {
    //   $course_id = 1;
    // } elseif ($course=="セルフコース") {
    //   $course_id = 2;
    // } else {
    // }

    // 登録情報
    $member_data = array(
      'member_name' => $_POST['name'],
      'select_course' => $_POST['course'],
      'login_mail' => $_POST['mail'],
      'login_password' => $_POST['password'],
    );

    // TODO 必須チェックとメアド重複チェック、及びエラー時のメッセージ

    echo $member_data[0];

    $success = $member_model->insert($member_data);
    if($success) {
      header("Location: list-member.php");
      exit;
    } else {
      echo "登録に失敗しました。";
    }

  }

?>