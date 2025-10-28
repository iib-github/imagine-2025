<?php
require_once dirname(__FILE__) . '/../scripts/env.php';
require_once dirname(__FILE__) . '/../scripts/model/MemberModel.class.php';
require_once dirname(__FILE__) . '/../scripts/model/ContactModel.class.php';

// .envファイルを読み込む
loadEnv();

function sendInquiry($mid, $category, $content) {
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

  //文字コード設定
  mb_language("Japanese");
  mb_internal_encoding("UTF-8");

  // 環境変数からメール設定を取得
  $mail_from_address = env('MAIL_FROM_ADDRESS', 'mail@cosmamic-space.com');
  $mail_from_name = env('MAIL_FROM_NAME', 'THE Imagine メンバーズ');
  $mail_inquiry_to = env('MAIL_INQUIRY_TO', 'starbow737@gmail.com,mail@cosmamic-space.com');

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

return mb_send_mail($to, $subject, $body, $header);
}

?>