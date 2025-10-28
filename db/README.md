# データベース設計・実装ファイル

このディレクトリには、フェーズ1のデータベース設計・テーブル作成に関するファイルが含まれています。

## ファイル構成

### 設計書
- `01_database_design.md` - データベース設計書
  - 既存テーブル構造の分析
  - 新規テーブル設計
  - 既存テーブルの変更内容
  - データ移行計画
  - パフォーマンス考慮事項

### SQLスクリプト
- `02_create_tables.sql` - 新規テーブル作成SQL
  - `tag_master`テーブル作成
  - `content_tag_relation`テーブル作成
  - `content_video`テーブル作成
  - `content_master`テーブルへのフィールド追加
  - インデックス作成
  - サンプルタグデータ投入

- `03_migrate_data.sql` - データ移行スクリプト
  - 既存動画データの移行
  - 既存コンテンツのコース設定
  - 移行後の確認クエリ

- `04_rollback_script.sql` - ロールバックスクリプト
  - 新規テーブルの削除
  - 追加フィールドの削除
  - 追加インデックスの削除

- `05_verification_queries.sql` - 検証クエリ
  - テーブル構造の確認
  - インデックスの確認
  - 外部キー制約の確認
  - データ件数の確認
  - サンプルデータの確認
  - パフォーマンステスト用クエリ
  - データ整合性の確認

## 実行手順

### 1. 本番環境での実行

```bash
# 1. バックアップの取得（必須）
mysqldump -u [username] -p [database_name] > backup_before_phase1.sql

# 2. テーブル作成・変更
mysql -u [username] -p [database_name] < 02_create_tables.sql

# 3. データ移行
mysql -u [username] -p [database_name] < 03_migrate_data.sql

# 4. 動作確認
mysql -u [username] -p [database_name] < 05_verification_queries.sql
```

### 2. ロールバックが必要な場合

```bash
# ロールバック実行
mysql -u [username] -p [database_name] < 04_rollback_script.sql
```

## 注意事項

### 実行前の確認事項
1. **データベースのバックアップ**を必ず取得してください
2. 既存の`content_master`テーブルに`content_movie_url`フィールドが存在することを確認
3. 既存の`member_master`テーブルに`select_course`フィールドが存在することを確認
4. 既存の`member_content_relation`テーブルで進捗管理が行われていることを確認
5. 既存の`category_master`テーブルに`delete_flag`フィールドが存在することを確認

### 実行後の確認事項
1. 新規テーブルが正常に作成されていること
2. 既存データが正常に移行されていること
3. 外部キー制約が正しく設定されていること
4. インデックスが適切に作成されていること
5. 既存の進捗管理システム（`member_content_relation`）との整合性を確認
6. 既存の`member_master`の`progress_*`フィールドとの整合性を確認

### トラブルシューティング
- 外部キー制約エラーが発生した場合、既存テーブルの構造を確認してください
- データ移行でエラーが発生した場合、`content_master`テーブルの`content_movie_url`フィールドの値を確認してください
- 進捗管理の整合性エラーが発生した場合、`member_content_relation`と`member_master`の`progress_*`フィールドの整合性を確認してください
- ロールバック後は、バックアップからデータを復元してください

### 既存データベース構造の重要な注意点
1. **進捗管理の二重構造**: `member_master`の`progress_*`フィールドと`member_content_relation`テーブルの両方で進捗管理が行われています
2. **コース設定の値**: `select_course`の値（1=プレミアム、2=ベーシック、3=その他）は既存データを維持し、表示名のみ変更します
3. **動画データの形式**: `content_movie_url`にはVimeoの埋め込みコードが格納されているため、移行時は`text`型の`video_url`フィールドに格納します
4. **論理削除**: `category_master`テーブルには`delete_flag`フィールドがあり、論理削除に対応しています

## 次のステップ

フェーズ1完了後は、以下のフェーズに進んでください：
- フェーズ2: モデルクラスの実装
- フェーズ3: 管理画面の実装
- フェーズ4: 会員画面の実装
