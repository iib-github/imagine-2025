<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  $session = Session::getInstance();
  $session->destroy();
  header("location: login.php");
  exit;
