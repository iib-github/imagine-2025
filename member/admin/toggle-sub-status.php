<?php
require_once dirname(__FILE__) . '/../scripts/env.php';
require_once dirname(__FILE__) . '/../scripts/Session.class.php';
require_once dirname(__FILE__) . '/../scripts/model/SubModel.class.php';

loadEnv();
initializeErrorHandling();

$session = Session::getInstance();
if($session->get('admin') === false) {
  header('Location: login.php');
  exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: list-sub.php');
  exit;
}

$sub_id = isset($_POST['sub_id']) ? (int)$_POST['sub_id'] : 0;
$target_status = isset($_POST['target_status']) ? (int)$_POST['target_status'] : 0;
$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'list-sub.php';

if($sub_id <= 0 || ($target_status !== SubModel::ACTIVE && $target_status !== SubModel::INACTIVE)) {
  header('Location: list-sub.php?status=toggled');
  exit;
}

$sub_model = new SubModel();
$update_data = array(
  'sub_id' => $sub_id,
  'indicate_flag' => $target_status,
);

$sub_model->registerSub($update_data);

if(strpos($redirect, 'list-sub.php') !== 0) {
  $redirect = 'list-sub.php';
}

$redirect .= (strpos($redirect, '?') !== false) ? '&status=toggled' : '?status=toggled';

header('Location: ' . $redirect);
exit;

