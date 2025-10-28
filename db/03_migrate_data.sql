-- =============================================
-- データ移行スクリプト
-- フェーズ1: 既存データの移行
-- =============================================

-- 1. 既存動画データの移行
-- content_master.content_movie_urlをcontent_videoテーブルに移行
-- 注意: content_movie_urlはVimeoの埋め込みコード（varchar(10000)）を格納
INSERT INTO `content_video` (
    `content_id`,
    `video_url`,
    `video_title`,
    `display_order`,
    `created_date`,
    `modified_date`
)
SELECT 
    `content_id`,
    `content_movie_url`,
    `content_title`,
    1 as `display_order`,
    `created_date`,
    `modified_date`
FROM `content_master` 
WHERE `content_movie_url` IS NOT NULL 
AND `content_movie_url` != ''
AND `content_movie_url` != 'null'
AND LENGTH(`content_movie_url`) > 10; -- 有効な埋め込みコードのみ移行

-- 2. 既存コンテンツのコース設定
-- 既存のcontent_masterレコードにtarget_courseフィールドを設定
UPDATE `content_master` 
SET `target_course` = 'all' 
WHERE `target_course` IS NULL;

-- 3. 既存のmember_masterのselect_course値を確認・調整
-- 既存データ: 1=プレミアム、2=ベーシック、3=その他
-- 新規設計: 1=スタンダード、2=ベーシック、3=アドバンス
-- 既存の値はそのまま維持（管理画面で後から変更可能）

-- 4. 既存の進捗管理データの確認
-- member_masterテーブルのprogress_*フィールドと
-- member_content_relationテーブルの整合性を確認
SELECT 
    'member_master進捗データ' as data_type,
    COUNT(*) as total_members,
    SUM(CASE WHEN progress_first > 0 THEN 1 ELSE 0 END) as first_progress,
    SUM(CASE WHEN progress_second > 0 THEN 1 ELSE 0 END) as second_progress,
    SUM(CASE WHEN progress_third > 0 THEN 1 ELSE 0 END) as third_progress
FROM member_master;

SELECT 
    'member_content_relationデータ' as data_type,
    COUNT(*) as total_relations,
    COUNT(DISTINCT member_id) as unique_members,
    COUNT(DISTINCT content_id) as unique_contents
FROM member_content_relation;

-- =============================================
-- 移行後の確認クエリ
-- =============================================

-- 移行された動画データの確認
SELECT 
    cv.video_id,
    cv.content_id,
    cm.content_title,
    cv.video_title,
    cv.display_order,
    cv.created_date
FROM `content_video` cv
JOIN `content_master` cm ON cv.content_id = cm.content_id
ORDER BY cv.content_id, cv.display_order;

-- コース設定の確認
SELECT 
    content_id,
    content_title,
    target_course,
    created_date
FROM `content_master`
ORDER BY content_id;

-- タグマスターデータの確認
SELECT 
    tag_id,
    tag_name,
    tag_description,
    created_date
FROM `tag_master`
ORDER BY tag_id;
