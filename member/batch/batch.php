#!/usr/bin/env php
<?php
/***********************************************************/
/* 登録済みのお知らせやコンテンツを取得し、公開日を判定行って公開するスクリプト */
/***********************************************************/
// 注意: シェバン行（#!/usr/bin/env php）は環境によって変更が必要な場合があります
// .envファイルに PHP_PATH を記載していますが、シェバン行は直接置き換えできません
// 実際の実行時は、cronやコマンドラインで env('PHP_PATH') を使うか、適切なPHPパスを設定してください

require_once dirname(__FILE__) . '/../scripts/env.php';
require_once dirname(__FILE__) . '/../scripts/BaseModel.class.php';
require_once dirname(__FILE__) . '/../scripts/model/NewsModel.class.php';
require_once dirname(__FILE__) . '/../scripts/model/CategoryModel.class.php';
require_once dirname(__FILE__) . '/../scripts/model/ContentModel.class.php';

// .envファイルを読み込む
loadEnv();

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
