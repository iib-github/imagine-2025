<?php
require_once dirname(__FILE__) . '/../scripts/env.php';
require_once dirname(__FILE__) . '/../scripts/Session.class.php';
require_once dirname(__FILE__) . '/../scripts/model/ContentModel.class.php';

loadEnv();
initializeErrorHandling();

$session = Session::getInstance();
if($session->get('admin') === false) {
  header('Location: login.php');
  exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: list-content.php');
  exit;
}

$content_id = isset($_POST['content_id']) ? (int)$_POST['content_id'] : 0;
$target_status = isset($_POST['target_status']) ? (int)$_POST['target_status'] : 0;
$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'list-content.php';

if($content_id <= 0 || ($target_status !== ContentModel::ACTIVE && $target_status !== ContentModel::INACTIVE)) {
  header('Location: list-content.php?status=toggled');
  exit;
}

$content_model = new ContentModel();
$update_data = array(
  'indicate_flag' => $target_status,
  'content_id' => $content_id,
);

$result = $content_model->registerContent($update_data);

if(strpos($redirect, 'list-content.php') !== 0) {
  $redirect = 'list-content.php';
}

if(strpos($redirect, '?') !== false) {
  $redirect .= '&status=toggled';
} else {
  $redirect .= '?status=toggled';
}

header('Location: ' . $redirect);
exit;

