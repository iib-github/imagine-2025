-- =============================================
-- 既存テーブルにtarget_courseカラムを追加
-- フェーズ1: データベース設計・テーブル作成
-- =============================================

-- 1. category_masterテーブルにtarget_courseフィールドを追加
ALTER TABLE `category_master` 
ADD COLUMN `target_course` varchar(20) DEFAULT 'all' COMMENT '表示対象コース（basic,advance,all）' 
AFTER `delete_flag`;

-- 2. news_masterテーブルにtarget_courseフィールドを追加
ALTER TABLE `news_master` 
ADD COLUMN `target_course` varchar(20) DEFAULT 'all' COMMENT '表示対象コース（basic,advance,all）' 
AFTER `indicate_flag`;

-- 3. sub_masterテーブルにtarget_courseフィールドを追加
ALTER TABLE `sub_master` 
ADD COLUMN `target_course` varchar(20) DEFAULT 'all' COMMENT '表示対象コース（basic,advance,all）' 
AFTER `indicate_flag`;

-- 4. upload_masterテーブルにtarget_courseフィールドを追加
ALTER TABLE `upload_master` 
ADD COLUMN `target_course` varchar(20) DEFAULT 'all' COMMENT '表示対象コース（basic,advance,all）' 
AFTER `indicate_flag`;

-- =============================================
-- 既存データの更新
-- =============================================

-- 既存のレコードにtarget_courseを'all'に設定
UPDATE `category_master` SET `target_course` = 'all' WHERE `target_course` IS NULL;
UPDATE `news_master` SET `target_course` = 'all' WHERE `target_course` IS NULL;
UPDATE `sub_master` SET `target_course` = 'all' WHERE `target_course` IS NULL;
UPDATE `upload_master` SET `target_course` = 'all' WHERE `target_course` IS NULL;

-- =============================================
-- インデックスの追加（パフォーマンス向上）
-- =============================================

-- target_courseカラムにインデックスを追加
ALTER TABLE `category_master` ADD INDEX `idx_target_course` (`target_course`);
ALTER TABLE `news_master` ADD INDEX `idx_target_course` (`target_course`);
ALTER TABLE `sub_master` ADD INDEX `idx_target_course` (`target_course`);
ALTER TABLE `upload_master` ADD INDEX `idx_target_course` (`target_course`);
