<?php
  require_once dirname(__FILE__) . '/scripts/env.php';
  require_once dirname(__FILE__) . '/scripts/model/MemberModel.class.php';

  header('Content-Type: application/json; charset=utf-8');

  $response = array(
    'success' => false,
    'message' => '送信に失敗しました。時間をおいて再度お試しください。'
  );

  try {
    loadEnv();
    initializeErrorHandling();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      $response['message'] = '不正なリクエストです。';
      echo json_encode($response, JSON_UNESCAPED_UNICODE);
      exit;
    }

    $mail = isset($_POST['login_mail']) ? trim($_POST['login_mail']) : '';
    if ($mail === '' || !filter_var($mail, FILTER_VALIDATE_EMAIL)) {
      $response['message'] = 'メールアドレスの形式が正しくありません。';
      echo json_encode($response, JSON_UNESCAPED_UNICODE);
      exit;
    }

    $member_model = new MemberModel();
    $member = $member_model->getMemberByMail($mail);

    // メール送信準備
    mb_language("Japanese");
    mb_internal_encoding("UTF-8");

    $mail_from_address = env('MAIL_FROM_ADDRESS', 'mail@cosmamic-space.com');
    $site_base_url = env('SITE_BASE_URL', 'https://business-quest.link/member');
    $subject = "【THE Imagine】パスワードのご案内";
    $header = "From: " . $mail_from_address;

    if (!empty($member)) {
      $body = "{$member['member_name']} 様

THE Imagine 事務局です。
パスワードの再送をご希望とのことでご案内いたします。

----------------------------------------
ログインURL
{$site_base_url}/login.php

ログインメールアドレス：
{$member['login_mail']}

パスワード：
{$member['login_password']}
----------------------------------------

引き続きよろしくお願いいたします。

THE Imagine 事務局
";

      if (!mb_send_mail($mail, $subject, $body, $header, '-f ' . $mail_from_address)) {
        throw new Exception('メール送信に失敗しました。');
      }
    }

    // メール送信に成功、または登録がなかった場合も成功レスポンスを返す（情報漏えい防止）
    $response['success'] = true;
    $response['message'] = '入力いただいたメールアドレス宛にパスワード再送メールを送信しました。ご確認ください。';

  } catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = '送信に失敗しました。時間をおいて再度お試しください。';
  }

  echo json_encode($response, JSON_UNESCAPED_UNICODE);

