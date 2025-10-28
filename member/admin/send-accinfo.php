<?php
  require_once dirname(__FILE__) . '/../scripts/env.php';
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/MemberModel.class.php';
  
  // .envファイルを読み込む
  loadEnv();
  
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('admin') === false) {
    header("Location: login.php");
    exit;
  }

  // GETパラメーターから会員IDが取れなければ会員一覧画面に飛ばす。
  if(empty($_GET['mid'])) {
    header("Location: list-member.php");
    exit;
  }

  // 連絡する会員情報取得
  $member_model = new MemberModel();
  $member = $member_model->select(array('member_id'=>$_GET['mid']));
  $member = $member[0];

  //文字コード設定
  mb_language("Japanese");
  mb_internal_encoding("UTF-8");

  // 環境変数からメール設定を取得
  $mail_from_address = env('MAIL_FROM_ADDRESS', 'mail@cosmamic-space.com');
  $site_base_url = env('SITE_BASE_URL', 'https://business-quest.link/member');

  $subject = "アカウント情報のご連絡 THE Imagine"; // メール件名
  $to = $member['login_mail']; // 宛先
  $header = "From: " . $mail_from_address; // 差出人

  $body = "
${member['member_name']} 様

星野ワタルです。

改めましてみなさん
THE Imagineへご参加ありがとうございます！


 いよいよ今日から
１６週間のコンテンツ配信がはじまりますね。


1週目は・・・
「THE Imagine へようこそ」
「ホロスコープと星座一覧を作成しよう！」



こちらの
2本の動画をお届けいたします。



動画と資料は
会員専用サイトより視聴&ダウンロードが可能です。
早速、こちらからご覧ください！
　

会員サイトURL
${site_base_url}/login.php

ユーザー名：${member['login_mail']}
パスワード：${member['login_password']}


=================================

【The Imagine】事務局

ご不明点などある際には
こちらまで連絡お願いします。

${mail_from_address}

=================================

";

  if(mb_send_mail($to, $subject, $body, $header, '-f ' . $mail_from_address)) {
  // if(mb_send_mail($to, $subject, $body, $header)) {
    // メール送信に成功したら、送信済みフラグを立てる。
    $member_model->update(array('is_contacted'=>2), array('member_id'=>$member['member_id']));
    header("Location: list-member.php");
    exit;
  }

?>