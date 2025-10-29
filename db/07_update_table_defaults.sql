-- =============================================
-- テーブルのデフォルト値更新SQL
-- =============================================

-- content_videoテーブルのcreated_dateとmodified_dateにDEFAULT CURRENT_TIMESTAMPを設定
ALTER TABLE `content_video` 
MODIFY COLUMN `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '作成日時',
MODIFY COLUMN `modified_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新日時';

-- tag_masterテーブルのcreated_dateとmodified_dateにDEFAULT CURRENT_TIMESTAMPを設定
ALTER TABLE `tag_master` 
MODIFY COLUMN `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '作成日時',
MODIFY COLUMN `modified_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新日時';

-- content_tag_relationテーブルのcreated_dateにDEFAULT CURRENT_TIMESTAMPを設定
ALTER TABLE `content_tag_relation` 
MODIFY COLUMN `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '作成日時';
