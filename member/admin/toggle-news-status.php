<?php
require_once dirname(__FILE__) . '/../scripts/env.php';
require_once dirname(__FILE__) . '/../scripts/Session.class.php';
require_once dirname(__FILE__) . '/../scripts/model/NewsModel.class.php';

loadEnv();
initializeErrorHandling();

$session = Session::getInstance();
if($session->get('admin') === false) {
  header('Location: login.php');
  exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: list-news.php');
  exit;
}

$news_id = isset($_POST['news_id']) ? (int)$_POST['news_id'] : 0;
$target_status = isset($_POST['target_status']) ? (int)$_POST['target_status'] : null;
$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'list-news.php';

if($news_id <= 0 || !in_array($target_status, array(0, 1), true)) {
  header('Location: list-news.php?status=toggled');
  exit;
}

$news_model = new NewsModel();
$update_data = array(
  'is_active' => $target_status,
);

$news_model->update($update_data, array('id' => $news_id));

if(strpos($redirect, 'list-news.php') !== 0) {
  $redirect = 'list-news.php';
}

$redirect .= (strpos($redirect, '?') !== false) ? '&status=toggled' : '?status=toggled';

header('Location: ' . $redirect);
exit;

