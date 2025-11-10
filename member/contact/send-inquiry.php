<?php
require_once dirname(__FILE__) . '/../scripts/env.php';
require_once dirname(__FILE__) . '/../scripts/model/MemberModel.class.php';
require_once dirname(__FILE__) . '/../scripts/model/ContactModel.class.php';

// .envファイルを読み込む
loadEnv();

function sendInquiry($mid, $category, $content) {
  $log_path = env('CONTACT_LOG_PATH', '');
  $logger = function($message) use ($log_path) {
    $prefix = '[ContactInquiry] ' . date('c') . ' ';
    if (!empty($log_path)) {
      error_log($prefix . $message . PHP_EOL, 3, $log_path);
    } else {
      error_log($prefix . $message);
    }
  };

  // メンバー情報取得
  $member_model = new MemberModel();
  $member = $member_model->select(array('member_id'=>$mid));
  $member = $member[0];
  $name = $member['member_name'];
  $mail = $member['login_mail'];

  //データベース
  $contact_model = new ContactModel();
  // 登録情報
  $data = array(
    'member_id' => $mid,
    'category' => $category,
    'text' => $content,
  );
  $success = $contact_model->registerContact($data);
  if(!$success) {
    $logger("Failed to register contact in database (member_id={$mid}).");
  }

  //文字コード設定
  mb_language("Japanese");
  mb_internal_encoding("UTF-8");

  // 環境変数からメール設定を取得
  $mail_from_address = env('MAIL_FROM_ADDRESS', 'info@the-imagine.com');
  $mail_from_name = env('MAIL_FROM_NAME', 'THE Imagine メンバーズ');
  $mail_inquiry_to = env('MAIL_INQUIRY_TO', 'starbow737@gmail.com,info@the-imagine.com,mail@cosmamic-space.com');

  $subject = "THE Imagine会員様よりご質問がありました。"; // メール件名
  $to = $mail_inquiry_to; // 宛先
  $header = "From: " . mb_encode_mimeheader($mail_from_name) . "<" . $mail_from_address . ">"; // 差出人

  $body = "
THE Imagine会員様より、ご質問がありました。
以下の内容をご確認お願いします。

-----------------
会員名：
${name}

お名前（ハンドルネーム）：
${category}

メールアドレス：
${mail}

ご質問内容：
${content}
-----------------

";

  $mail_result = mb_send_mail($to, $subject, $body, $header);
  if(!$mail_result) {
    $logger("Failed to send inquiry mail (to={$to}, member_id={$mid}).");
  }

  // Chatwork通知設定
  $chatwork_token = env('CHATWORK_API_TOKEN', '');
  $chatwork_room_id = env('CHATWORK_ROOM_ID', '');

  if (!empty($chatwork_token) && !empty($chatwork_room_id)) {
    $chatwork_message = "[info][title]THE Imagine会員様よりご質問がありました。[/title]"
      . "会員名：${name}\n"
      . "お名前（ハンドルネーム）：${category}\n"
      . "メールアドレス：${mail}\n"
      . "ご質問内容：\n${content}\n"
      . "[/info]";

    $endpoint = "https://api.chatwork.com/v2/rooms/" . urlencode($chatwork_room_id) . "/messages";
    $payload = http_build_query(array('body' => $chatwork_message), '', '&');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'X-ChatWorkToken: ' . $chatwork_token,
    ));
    $response = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if($response === false || $curl_errno !== 0) {
      $logger("Failed to post inquiry to Chatwork (member_id={$mid}, curl_errno={$curl_errno}).");
    } elseif($http_status < 200 || $http_status >= 300) {
      $logger("Chatwork API returned status {$http_status} for member_id={$mid}. response={$response}");
    }
  } else {
    $logger("Chatwork notification skipped (missing token or room id).");
  }

  return $mail_result;
}

?>