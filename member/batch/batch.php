#!/usr/local/php/7.3/bin/php
<?php
/***********************************************************/
/* 登録済みのお知らせやコンテンツを取得し、公開日を判定行って公開するスクリプト */
/***********************************************************/
require_once '/home/users/2/doinatsumi/web/the-imagine.com/member/member/scripts/BaseModel.class.php';
require_once '/home/users/2/doinatsumi/web/the-imagine.com/member/member/scripts/model/NewsModel.class.php';
require_once '/home/users/2/doinatsumi/web/the-imagine.com/member/member/scripts/model/CategoryModel.class.php';
require_once '/home/users/2/doinatsumi/web/the-imagine.com/member/member/scripts/model/ContentModel.class.php';

$news_model = new NewsModel();
$category_model = new CategoryModel();
$content_model = new ContentModel();

// 非公開となっているお知らせ一覧を取得
$news_list = $news_model->getInActiveNewsList();
// 非公開となっているカテゴリ一覧を取得
$category_list = $category_model->getInActiveCategoryList();
// 非公開となっているコンテンツ一覧を取得
$cotent_list = $content_model->getInActiveContentList();

// ※以下の処理はループの中でクエリを投げてしまってるけど改修大変だし、夜間バッチだし良しとする。

$now_date = date('Y.m.d'); // 今日の日付

// 公開日時が今日になっているお知らせを公開状態とする。
// 注：公開日の「時分」は無視する。
foreach ($news_list as $news) {
  $pub_date = mb_substr($news['note_date'], 0 , 10);

  if($now_date == $pub_date) {
    $news_model->update(array('is_active'=>1), array('id'=>$news['id']));
  }
}

// 公開日時が今日になっているカテゴリを公開状態とする。
foreach ($category_list as $category) {
  $pub_date = mb_substr($category['pub_date'], 0 , 10);

  if($now_date == $pub_date) {
    $category_model->update(array('indicate_flag'=>1), array('category_id'=>$category['category_id']));
  }
}

// 公開日時が今日になっているコンテンツを公開状態とする。
foreach ($cotent_list as $content) {
  $pub_date = mb_substr($content['pub_date'], 0 , 10);

  if($now_date == $pub_date) {
    $content_model->update(array('indicate_flag'=>1), array('content_id'=>$content['content_id']));
  }
}
