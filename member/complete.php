<?php
  require_once dirname(__FILE__) . '/scripts/model/MemberContentRelation.class.php';

  // 直接アクセスされたらログイン画面へ飛ばす。
  if(empty($_POST['m_id'])) {
    header("Location: login.php");
    exit;
  }

  // 達成した課題をインサート
  $membCntRel = new MemberContentRelation();
  $success = $membCntRel->insert(array(
    'member_id' => $_POST['m_id'],
    'category_id' => $_POST['ctg_id'],
    'content_id' => $_POST['cont_id'],
  ));

  if($success) {
    echo 'success';
  } else {
    echo 'fail';
  }
?>