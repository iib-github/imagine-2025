-- =============================================
-- 簡易検証クエリ（phpMyAdmin用）
-- =============================================

-- 1. テーブル一覧の確認
SHOW TABLES;

-- 2. データ件数の確認
SELECT 'tag_master' as table_name, COUNT(*) as record_count FROM `tag_master`
UNION ALL
SELECT 'content_tag_relation' as table_name, COUNT(*) as record_count FROM `content_tag_relation`
UNION ALL
SELECT 'content_video' as table_name, COUNT(*) as record_count FROM `content_video`
UNION ALL
SELECT 'content_master' as table_name, COUNT(*) as record_count FROM `content_master`;

-- 3. タグマスターのサンプルデータ
SELECT * FROM `tag_master` ORDER BY `tag_id` LIMIT 5;

-- 4. コンテンツ動画のサンプルデータ
SELECT 
    cv.video_id,
    cv.content_id,
    cm.content_title,
    cv.video_title,
    cv.display_order
FROM `content_video` cv
JOIN `content_master` cm ON cv.content_id = cm.content_id
ORDER BY cv.content_id, cv.display_order
LIMIT 5;

-- 5. コース設定の確認
SELECT 
    content_id,
    content_title,
    target_course,
    indicate_flag
FROM `content_master`
WHERE target_course IS NOT NULL
ORDER BY content_id
LIMIT 5;
