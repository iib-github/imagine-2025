-- =============================================
-- ロールバックスクリプト
-- フェーズ1の変更を元に戻す場合に使用
-- =============================================

-- 注意: このスクリプトは既存データに影響を与える可能性があります
-- 実行前に必ずバックアップを取得してください

-- 1. 新規作成したテーブルを削除（外部キー制約により自動的に関連データも削除される）
DROP TABLE IF EXISTS `content_video`;
DROP TABLE IF EXISTS `content_tag_relation`;
DROP TABLE IF EXISTS `tag_master`;

-- 2. content_masterテーブルから追加したフィールドを削除
ALTER TABLE `content_master` DROP COLUMN IF EXISTS `target_course`;

-- 3. 追加したインデックスを削除
ALTER TABLE `content_master` DROP INDEX IF EXISTS `idx_target_course`;

-- =============================================
-- ロールバック後の確認
-- =============================================

-- テーブル一覧の確認
SHOW TABLES;

-- content_masterテーブルの構造確認
DESCRIBE `content_master`;
