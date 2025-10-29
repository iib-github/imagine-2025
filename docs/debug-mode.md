# デバッグモード（エラー表示制御）

## 概要

このプロジェクトでは、環境変数を使ってサイト全体でエラーの表示/非表示を切り替えることができます。

## 設定方法

### 1. `.env`ファイルを作成

プロジェクトのルートディレクトリに `.env` ファイルを作成します。`.env.example` をテンプレートとして使用してください。

```bash
cp .env.example .env
```

### 2. `DEBUG_MODE` を設定

`.env` ファイルの `DEBUG_MODE` を以下のいずれかの値に設定します。

#### デバッグモード（開発環境）を有効にする場合

```
DEBUG_MODE=true
# または
DEBUG_MODE=1
```

この設定により：
- PHPエラー、ワーニング、注意が画面に表示されます
- エラーログは同時にファイルにも記録されます（`logs/error.log`）

#### 本番モード（エラーを非表示にする場合）

```
DEBUG_MODE=false
# または
DEBUG_MODE=0
```

この設定により：
- PHPエラーは画面に表示されません
- すべてのエラーは `logs/error.log` ファイルに記録されます
- ユーザーには安全なエラーページが表示されます

## ファイル構成

```
imagine-2025/
├── .env                    # 環境変数設定ファイル（git管理外）
├── .env.example            # 環境変数テンプレート（バージョン管理対象）
├── logs/                   # エラーログディレクトリ（git管理外）
│   └── error.log          # エラーログファイル
└── member/
    └── scripts/
        └── env.php        # 環境変数とエラー処理ユーティリティ
```

## 使用例

### デバッグモード（開発環境）

`.env` ファイルの内容：

```
DEBUG_MODE=true
DB_NAME=imagine_portal
DB_ADDR=localhost
DB_USER=root
DB_PASS=password
```

この状態でPHPエラーが発生した場合：
- エラー内容が画面に表示されます
- エラーログが `logs/error.log` に記録されます

### 本番モード

`.env` ファイルの内容：

```
DEBUG_MODE=false
DB_NAME=imagine_portal
DB_ADDR=localhost
DB_USER=root
DB_PASS=password
```

この状態でPHPエラーが発生した場合：
- ユーザーには何も表示されません（安全性確保）
- エラー詳細は `logs/error.log` に記録されます
- 管理者はログを確認してトラブルシューティングできます

## ログファイルの確認

エラーログは `logs/error.log` ファイルに記録されます。

```bash
# ログファイルの内容を表示
tail -f logs/error.log

# ログファイルをクリア
rm logs/error.log
```

## 実装されているファイル

以下のファイルでデバッグモード設定が自動的に適用されます：

### 管理画面
- `member/admin/register-content.php`
- `member/admin/edit-content.php`
- その他の管理画面ファイル

### 会員画面
- `member/index.php`
- `member/detail.php`
- `member/list.php`
- その他の会員画面ファイル

## 注意事項

1. **本番環境での安全性**：
   - 本番環境では必ず `DEBUG_MODE=false` に設定してください
   - デバッグモードを有効にしたままにするとセキュリティリスクになります

2. **ログファイルの管理**：
   - ログファイルが大きくなる可能性があるため、定期的に確認・削除してください
   - ログディレクトリは書き込み権限が必要です

3. **エラーハンドリング**：
   - 本番モード中もエラーはすべて記録されます
   - エラーの詳細を確認する必要がある場合はログファイルを確認してください

## 新しいPHPファイルの追加時

新しいPHPファイルを作成する際は、以下のコードをファイルの先頭に追加してください：

```php
<?php
  require_once dirname(__FILE__) . '/scripts/env.php';
  // その他の require_once ...
  
  // .envファイルを読み込み、エラーハンドリングを初期化
  loadEnv();
  initializeErrorHandling();
  
  // その他のコード ...
?>
```

これにより、新しいファイルでも自動的にデバッグモード設定が適用されます。
