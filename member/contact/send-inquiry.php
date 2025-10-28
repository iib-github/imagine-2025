<?php
require_once dirname(__FILE__) . '/../scripts/model/MemberModel.class.php';
require_once dirname(__FILE__) . '/../scripts/model/ContactModel.class.php';

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

  $subject = "THE Imagine会員様よりご質問がありました。"; // メール件名
  $to = 'starbow737@gmail.com,mail@cosmamic-space.com'; // 宛先
  // $to = 't.yoshimi@i-i-b.jp'; // 宛先
  $header = "From: " .mb_encode_mimeheader("THE Imagine メンバーズ") ."<mail@cosmamic-space.com>"; // 差出人

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