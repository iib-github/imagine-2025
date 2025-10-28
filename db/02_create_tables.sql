-- =============================================
-- 新規テーブル作成SQL
-- フェーズ1: データベース設計・テーブル作成
-- =============================================

-- 1. tag_master（タグマスター）テーブル作成
CREATE TABLE `tag_master` (
  `tag_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'タグID',
  `tag_name` varchar(50) NOT NULL COMMENT 'タグ名',
  `tag_description` text COMMENT 'タグの説明',
  `created_date` datetime NOT NULL COMMENT '作成日時',
  `modified_date` datetime NOT NULL COMMENT '更新日時',
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `tag_name` (`tag_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='タグマスター';

-- 2. content_tag_relation（コンテンツとタグの関連）テーブル作成
CREATE TABLE `content_tag_relation` (
  `relation_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '関連ID',
  `content_id` int(11) NOT NULL COMMENT 'コンテンツID',
  `tag_id` int(11) NOT NULL COMMENT 'タグID',
  `created_date` datetime NOT NULL COMMENT '作成日時',
  PRIMARY KEY (`relation_id`),
  UNIQUE KEY `content_tag_unique` (`content_id`, `tag_id`),
  KEY `idx_content_id` (`content_id`),
  KEY `idx_tag_id` (`tag_id`),
  CONSTRAINT `fk_content_tag_content` FOREIGN KEY (`content_id`) REFERENCES `content_master` (`content_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_content_tag_tag` FOREIGN KEY (`tag_id`) REFERENCES `tag_master` (`tag_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='コンテンツとタグの関連';

-- 3. content_video（コンテンツ動画）テーブル作成
CREATE TABLE `content_video` (
  `video_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '動画ID',
  `content_id` int(11) NOT NULL COMMENT 'コンテンツID',
  `video_url` text NOT NULL COMMENT '動画URL（埋め込みコード）',
  `video_title` varchar(200) NOT NULL COMMENT '動画タイトル',
  `thumbnail_url` varchar(500) DEFAULT NULL COMMENT 'サムネイル画像URL',
  `display_order` int(11) NOT NULL DEFAULT 1 COMMENT '表示順序',
  `created_date` datetime NOT NULL COMMENT '作成日時',
  `modified_date` datetime NOT NULL COMMENT '更新日時',
  PRIMARY KEY (`video_id`),
  KEY `idx_content_id` (`content_id`),
  KEY `idx_content_display_order` (`content_id`, `display_order`),
  CONSTRAINT `fk_content_video_content` FOREIGN KEY (`content_id`) REFERENCES `content_master` (`content_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='コンテンツ動画';

-- =============================================
-- 既存テーブルの変更
-- =============================================

-- 4. content_masterテーブルにtarget_courseフィールドを追加
ALTER TABLE `content_master` 
ADD COLUMN `target_course` varchar(20) DEFAULT 'all' COMMENT '表示対象コース（standard,basic,advance,all）' 
AFTER `is_faq`;

-- 5. member_masterテーブルのselect_courseフィールドの値を拡張
-- 既存の値: 1=プレミアム、2=ベーシック、3=その他
-- 新規の値: 1=スタンダード（旧プレミアム）、2=ベーシック、3=アドバンス
-- 既存データはそのまま維持（後で管理画面で変更可能）

-- =============================================
-- インデックスの追加（パフォーマンス向上）
-- =============================================

-- content_masterテーブルのtarget_courseフィールドにインデックスを追加
ALTER TABLE `content_master` 
ADD INDEX `idx_target_course` (`target_course`);

-- =============================================
-- 初期データの投入（サンプルタグ）
-- =============================================

-- サンプルタグデータの投入
INSERT INTO `tag_master` (`tag_name`, `tag_description`, `created_date`, `modified_date`) VALUES
('牡羊座', '牡羊座に関するコンテンツ', NOW(), NOW()),
('牡牛座', '牡牛座に関するコンテンツ', NOW(), NOW()),
('双子座', '双子座に関するコンテンツ', NOW(), NOW()),
('蟹座', '蟹座に関するコンテンツ', NOW(), NOW()),
('獅子座', '獅子座に関するコンテンツ', NOW(), NOW()),
('乙女座', '乙女座に関するコンテンツ', NOW(), NOW()),
('天秤座', '天秤座に関するコンテンツ', NOW(), NOW()),
('蠍座', '蠍座に関するコンテンツ', NOW(), NOW()),
('射手座', '射手座に関するコンテンツ', NOW(), NOW()),
('山羊座', '山羊座に関するコンテンツ', NOW(), NOW()),
('水瓶座', '水瓶座に関するコンテンツ', NOW(), NOW()),
('魚座', '魚座に関するコンテンツ', NOW(), NOW()),
('太陽', '太陽に関するコンテンツ', NOW(), NOW()),
('月', '月に関するコンテンツ', NOW(), NOW()),
('水星', '水星に関するコンテンツ', NOW(), NOW()),
('金星', '金星に関するコンテンツ', NOW(), NOW()),
('火星', '火星に関するコンテンツ', NOW(), NOW()),
('木星', '木星に関するコンテンツ', NOW(), NOW()),
('土星', '土星に関するコンテンツ', NOW(), NOW()),
('基礎', '基礎的な内容', NOW(), NOW()),
('応用', '応用的な内容', NOW(), NOW()),
('実践', '実践的な内容', NOW(), NOW());
