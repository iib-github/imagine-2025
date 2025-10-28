# データベース設計書

## 既存テーブル構造の分析

### 既存テーブル一覧
- `member_master` - 会員情報
- `category_master` - カテゴリー情報  
- `content_master` - コンテンツ情報
- `news_master` - お知らせ情報
- `contact_master` - 問い合わせ情報
- `sub_master` - サブコンテンツ情報
- `upload_master` - アップロード情報
- `comment_master` - コメント情報
- `member_content_relation` - 会員とコンテンツの関連

### 既存テーブルの主要フィールド

#### member_master
- `member_id` (PK) - 会員ID
- `member_name` - 会員名
- `select_course` - 選択コース（1:プレミアム、2:ベーシック、3:その他）
- `login_mail` - ログインメールアドレス
- `login_password` - ログインパスワード
- `progress_first` - 第1カテゴリー進捗
- `progress_second` - 第2カテゴリー進捗
- `progress_third` - 第3カテゴリー進捗
- `progress_fourth` - 第4カテゴリー進捗
- `progress_fifth` - 第5カテゴリー進捗
- `progress_sixth` - 第6カテゴリー進捗
- `member_status` - 会員ステータス（1:有効）
- `is_contacted` - 連絡済みフラグ（1:連絡済み）
- `stop_date` - 退会日時

#### content_master
- `content_id` (PK) - コンテンツID
- `category_id` (FK) - カテゴリーID
- `content_week` - 週番号
- `content_title` - コンテンツタイトル
- `content_movie_url` - 動画埋め込みコード（既存の単一動画URL）
- `content_text` - 説明テキスト
- `thumbnail_url` - サムネイル画像URL
- `text_dl_url` - 講座資料ダウンロードURL
- `message_dl_url` - 文字起こし資料ダウンロードURL
- `display_order` - 表示順序
- `indicate_flag` - 表示フラグ（1:表示、2:非表示）
- `pub_date` - 公開日時
- `is_faq` - FAQフラグ（1:FAQ、2:非FAQ）

#### category_master
- `category_id` (PK) - カテゴリーID
- `category_number` - カテゴリー番号
- `category_title` - カテゴリータイトル
- `category_top_img` - トップバナー画像
- `category_list_img` - メイン画像
- `content_text` - カテゴリー説明テキスト
- `number_of_contents` - コンテンツ数
- `indicate_flag` - 表示フラグ（1:表示、2:非表示）
- `delete_flag` - 削除フラグ（1:有効、2:削除）
- `pub_date` - 公開日時

#### member_content_relation
- `id` (PK) - 関連ID
- `member_id` (FK) - 会員ID
- `category_id` (FK) - カテゴリーID
- `content_id` (FK) - コンテンツID
- `created_date` - 作成日時
- `modified_date` - 更新日時

#### sub_master
- `sub_id` (PK) - サブコンテンツID
- `category_id` (FK) - カテゴリーID
- `content_title` - サブコンテンツタイトル
- `thumbnail_url` - サムネイル画像URL
- `content_url` - コンテンツURL
- `content_text` - 説明テキスト
- `display_order` - 表示順序
- `indicate_flag` - 表示フラグ（1:表示、2:非表示）
- `pub_date` - 公開日時

#### news_master
- `news_id` (PK) - お知らせID
- `note_date` - お知らせ日時
- `note_title` - お知らせタイトル
- `note_text` - お知らせ本文
- `indicate_flag` - 表示フラグ（1:表示、2:非表示）
- `created_date` - 作成日時
- `modified_date` - 更新日時

#### comment_master
- `comment_id` (PK) - コメントID
- `member_id` (FK) - 会員ID
- `content_id` (FK) - コンテンツID
- `name` - コメント投稿者名
- `comment` - コメント本文
- `delete_flag` - 削除フラグ（1:有効、2:削除）
- `created_date` - 作成日時
- `modified_date` - 更新日時

#### contact_master
- `contact_id` (PK) - 問い合わせID
- `member_name` - 会員名
- `member_mail` - メールアドレス
- `contact_text` - 問い合わせ内容
- `created_date` - 作成日時
- `modified_date` - 更新日時

#### upload_master
- `upload_id` (PK) - アップロードID
- `member_id` (FK) - 会員ID
- `content_id` (FK) - コンテンツID
- `upload_url` - アップロードURL
- `upload_name` - アップロードファイル名
- `created_date` - 作成日時
- `modified_date` - 更新日時

---

## 新規テーブル設計

### 1. tag_master（タグマスター）

| フィールド名 | データ型 | 制約 | 説明 |
|-------------|----------|------|------|
| tag_id | INT | PRIMARY KEY, AUTO_INCREMENT | タグID |
| tag_name | VARCHAR(50) | NOT NULL, UNIQUE | タグ名（例：「獅子座」「金星」） |
| tag_description | TEXT | NULL | タグの説明（オプション） |
| created_date | DATETIME | NOT NULL | 作成日時 |
| modified_date | DATETIME | NOT NULL | 更新日時 |

**インデックス:**
- PRIMARY KEY: `tag_id`
- UNIQUE KEY: `tag_name`

### 2. content_tag_relation（コンテンツとタグの関連）

| フィールド名 | データ型 | 制約 | 説明 |
|-------------|----------|------|------|
| relation_id | INT | PRIMARY KEY, AUTO_INCREMENT | 関連ID |
| content_id | INT | NOT NULL, FOREIGN KEY | コンテンツID（content_master.content_id） |
| tag_id | INT | NOT NULL, FOREIGN KEY | タグID（tag_master.tag_id） |
| created_date | DATETIME | NOT NULL | 作成日時 |

**インデックス:**
- PRIMARY KEY: `relation_id`
- UNIQUE KEY: `content_tag_unique` (`content_id`, `tag_id`)
- INDEX: `idx_content_id` (`content_id`)
- INDEX: `idx_tag_id` (`tag_id`)

**外部キー制約:**
- `content_id` → `content_master.content_id` ON DELETE CASCADE
- `tag_id` → `tag_master.tag_id` ON DELETE CASCADE

### 3. content_video（コンテンツ動画）

| フィールド名 | データ型 | 制約 | 説明 |
|-------------|----------|------|------|
| video_id | INT | PRIMARY KEY, AUTO_INCREMENT | 動画ID |
| content_id | INT | NOT NULL, FOREIGN KEY | コンテンツID（content_master.content_id） |
| video_url | TEXT | NOT NULL | 動画URL（埋め込みコード） |
| video_title | VARCHAR(200) | NOT NULL | 動画タイトル |
| thumbnail_url | VARCHAR(500) | NULL | サムネイル画像URL（オプション） |
| display_order | INT | NOT NULL, DEFAULT 1 | 表示順序 |
| created_date | DATETIME | NOT NULL | 作成日時 |
| modified_date | DATETIME | NOT NULL | 更新日時 |

**インデックス:**
- PRIMARY KEY: `video_id`
- INDEX: `idx_content_id` (`content_id`)
- INDEX: `idx_content_display_order` (`content_id`, `display_order`)

**外部キー制約:**
- `content_id` → `content_master.content_id` ON DELETE CASCADE

---

## 既存テーブルの変更

### content_masterテーブルへの追加フィールド

| フィールド名 | データ型 | 制約 | 説明 |
|-------------|----------|------|------|
| target_course | VARCHAR(20) | NULL | 表示対象コース（'standard','basic','advance','all'） |

**デフォルト値:** `'all'`（全コース対象）

**コース設定値:**
- `'standard'` - スタンダード（プレミアム）コースのみ
- `'basic'` - ベーシックコースのみ  
- `'advance'` - アドバンスコースのみ
- `'all'` - 全コース対象

---

## データ移行計画

### 1. 既存動画データの移行

既存の`content_master.content_movie_url`を`content_video`テーブルに移行：

```sql
INSERT INTO content_video (content_id, video_url, video_title, display_order, created_date, modified_date)
SELECT 
    content_id,
    content_movie_url,
    content_title,
    1 as display_order,
    created_date,
    modified_date
FROM content_master 
WHERE content_movie_url IS NOT NULL 
AND content_movie_url != '';
```

### 2. 既存コンテンツのコース設定

既存の`content_master`レコードに`target_course`フィールドを設定：

```sql
-- デフォルトで全コース対象に設定
UPDATE content_master SET target_course = 'all' WHERE target_course IS NULL;
```

---

## パフォーマンス考慮事項

### インデックス設計
- タグ検索の高速化のため、`content_tag_relation`テーブルに適切なインデックスを設定
- コンテンツ一覧表示の高速化のため、`content_video`テーブルに`content_id`と`display_order`の複合インデックスを設定

### データ整合性
- 外部キー制約により、関連データの整合性を保証
- CASCADE削除により、親レコード削除時の子レコード自動削除

### 後方互換性
- 既存の`content_master.content_movie_url`フィールドは残存（移行後も参照可能）
- 既存の単一動画コンテンツも正常に表示されるよう実装

---

## 既存データベース構造の重要な発見事項

### 1. member_masterテーブルの進捗管理
既存の`member_master`テーブルには、各カテゴリーの進捗を個別に管理するフィールドが存在：
- `progress_first` - 第1カテゴリー進捗
- `progress_second` - 第2カテゴリー進捗
- `progress_third` - 第3カテゴリー進捗
- `progress_fourth` - 第4カテゴリー進捗
- `progress_fifth` - 第5カテゴリー進捗
- `progress_sixth` - 第6カテゴリー進捗

**影響**: 既存の`MemberModel::getScore()`メソッドは`member_content_relation`テーブルを使用しているが、これらの進捗フィールドとの整合性を確認する必要がある。

### 2. select_courseフィールドの値
既存データでは以下の値が使用されている：
- `1` - プレミアムコース（新設計では「スタンダード」に相当）
- `2` - ベーシックコース
- `3` - その他（新設計では「アドバンス」に相当）

**対応方針**: 既存データはそのまま維持し、管理画面での表示名のみ変更。

### 3. content_masterテーブルの動画URL
既存の`content_movie_url`フィールドは`varchar(10000)`で、Vimeoの埋め込みコードが格納されている。

**移行時の注意**: 既存の埋め込みコードを`content_video`テーブルに移行する際は、`video_url`フィールド（`text`型）に格納。

### 4. 既存の進捗管理システム
`member_content_relation`テーブルで会員のコンテンツ視聴履歴を管理している。

**考慮事項**: 新しい進捗表示機能では、既存の`MemberModel::getScore()`メソッドを活用し、`member_content_relation`テーブルのデータを使用する。

### 5. カテゴリー管理
既存の`category_master`テーブルには`delete_flag`フィールドがあり、論理削除に対応している。

**対応**: 新規テーブルでも論理削除パターンを採用することを検討。
