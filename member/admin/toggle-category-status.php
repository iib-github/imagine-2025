<?php
require_once dirname(__FILE__) . '/../scripts/env.php';
require_once dirname(__FILE__) . '/../scripts/Session.class.php';
require_once dirname(__FILE__) . '/../scripts/model/CategoryModel.class.php';

loadEnv();
initializeErrorHandling();

$session = Session::getInstance();
if($session->get('admin') === false) {
  header('Location: login.php');
  exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: list-category.php');
  exit;
}

$category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
$target_status = isset($_POST['target_status']) ? (int)$_POST['target_status'] : 0;
$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'list-category.php';

if($category_id <= 0 || ($target_status !== CategoryModel::ACTIVE && $target_status !== CategoryModel::INACTIVE)) {
  header('Location: list-category.php?status=toggled');
  exit;
}

$category_model = new CategoryModel();
$update_data = array(
  'category_id' => $category_id,
  'indicate_flag' => $target_status,
);

$category_model->registerCategory($update_data);

if(strpos($redirect, 'list-category.php') !== 0) {
  $redirect = 'list-category.php';
}

$redirect .= (strpos($redirect, '?') !== false) ? '&status=toggled' : '?status=toggled';

header('Location: ' . $redirect);
exit;

