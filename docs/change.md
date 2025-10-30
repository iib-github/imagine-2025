# プロジェクト複製時の変更箇所まとめ

このドキュメントは、既存のTHE Imagine Membersサイトを複製して新しいプロジェクトで使用する際に変更が必要な箇所をまとめたものです。

## プロジェクト概要

このプロジェクトは、PHPで構築された会員制コンテンツ配信システムです。

### 主な機能
- 会員ログイン・認証機能
- コンテンツ閲覧・ダウンロード機能
- カテゴリー管理
- お知らせ機能
- 問い合わせ機能
- 管理画面（会員管理、コンテンツ管理、カテゴリー管理など）
- 定期バッチ処理（自動公開機能）

### 技術スタック
- PHP 7.3以上（推奨）
- MySQL（PDO使用）
- セッション管理
- mbstring（日本語メール送信）

---

## 変更が必要な箇所

### 1. データベース接続設定

**ファイル**: `member/scripts/PdoInterface.class.php`

```3:6:member/scripts/PdoInterface.class.php
    const DB_NAME = "_the_imagine2020";
    const DB_ADDR = "mysql015.phy.heteml.lan";
    const DB_USER = "_the_imagine2020";
    const DB_PASS = "doinatsumi";
```

**変更内容**:
- `DB_NAME`: 新しいデータベース名に変更
- `DB_ADDR`: 新しいデータベースサーバーのホスト名またはIPアドレスに変更
- `DB_USER`: 新しいデータベースユーザー名に変更
- `DB_PASS`: 新しいデータベースパスワードに変更

**注意**: ローカル環境用のコメントアウトされた設定（21-26行目）も必要に応じて有効化してください。

---

### 2. セッション保存パス

**ファイル**: `member/scripts/Session.class.php`

```2:2:member/scripts/Session.class.php
  session_save_path(dirname(__FILE__).'/../../session');
```

**変更内容**:
- セッションディレクトリのパスを確認（相対パスで指定されているため、プロジェクト構造を維持すれば変更不要）
- `/session` ディレクトリが存在し、書き込み権限が設定されていることを確認

---

### 3. 管理画面ログイン情報

**ファイル**: `member/admin/login.php`

```3:4:member/admin/login.php
  define('ADMIN_ID', 'adm1n');
  define('ADMIN_PW', 'm7ufzanb');
```

**変更内容**:
- `ADMIN_ID`: 新しい管理画面のログインIDに変更
- `ADMIN_PW`: 新しい管理画面のパスワードに変更（セキュリティ上、強力なパスワードを推奨）

---

### 4. メール送信設定

#### 4-1. 問い合わせメール送信先

**ファイル**: `member/contact/send-inquiry.php`

```28:30:member/contact/send-inquiry.php
  $to = 'starbow737@gmail.com,mail@cosmamic-space.com'; // 宛先
  // $to = 't.yoshimi@i-i-b.jp'; // 宛先
  $header = "From: " .mb_encode_mimeheader("THE Imagine メンバーズ") ."<mail@cosmamic-space.com>"; // 差出人
```

**変更内容**:
- `$to`: 問い合わせメールの送信先アドレスを変更
- `$header`のFromアドレス: 新しい送信元メールアドレスに変更
- 送信者名（"THE Imagine メンバーズ"）も新しいサービス名に変更

#### 4-2. アカウント情報送信メール

**ファイル**: `member/admin/send-accinfo.php`

```29:29:member/admin/send-accinfo.php
  $header = "From: " . "mail@cosmamic-space.com"; // 差出人
```

```61:61:member/admin/send-accinfo.php
https://business-quest.link/member/login.php
```

```74:74:member/admin/send-accinfo.php
mail@cosmamic-space.com
```

```80:80:member/admin/send-accinfo.php
  if(mb_send_mail($to, $subject, $body, $header, '-f mail@cosmamic-space.com')) {
```

**変更内容**:
- `$header`のFromアドレス: 新しい送信元メールアドレスに変更
- メール本文内の会員サイトURL（61行目）: 新しいサイトURLに変更
- メール本文内の連絡先アドレス（74行目）: 新しい連絡先に変更
- `-f`パラメータ（80行目）: 新しい送信元アドレスに変更
- メール本文のサービス名・運営者名も適宜変更

#### 4-3. 問い合わせページの連絡先

**ファイル**: `member/contact/index.php`

```97:97:member/contact/index.php
          <a href="mailto:info@hoshino-wataru.com" style="text-decoration: underline;">info@hoshino-wataru.com</a>（THE Imagine事務局　あお）へご連絡ください。</p>
```

**変更内容**:
- メールアドレスと表示名を新しいサービスに合わせて変更

---

### 5. URLパス・ドメイン設定

以下のファイルで `/` というパスがハードコードされています。プロジェクトの配置に応じて変更してください。

**対象ファイル一覧**:
- `member/tmp/header.php` (複数箇所)
- `member/tmp/footer.php`
- `member/admin/edit-category.php`
- `member/admin/edit-content.php`
- `member/admin/edit-sub.php`
- `member/login.php`
- その他多数のPHPファイル（grep結果: 39箇所）

**変更例**:
```php
// 変更前
<a href="/">TOP</a>
<link rel="apple-touch-icon" href="/common/img/apple-touch-icon.png">

// 変更後（例：サブディレクトリに配置する場合）
<a href="/new-project/member/">TOP</a>
<link rel="apple-touch-icon" href="/new-project/member/common/img/apple-touch-icon.png">
```

**推奨対応**:
- 定数ファイルを作成し、ベースURLを一元管理することを推奨
- または、各ファイルでベースURLを動的に取得する実装に変更

---

### 6. ドメイン設定

#### 6-1. 外部リンク（プレ講座視聴ページ）

**ファイル**: 
- `member/tmp/header.php`
- `member/sidebar.php`
- `member/sidebar-contact.php`

```13:13:member/tmp/header.php
            <li><a href="https://the-imagine.com/membership/movie/session1/">プレ講座視聴ページ</a></li>
```

**変更内容**:
- 外部リンク先が不要な場合は削除
- 新しいサービス用のリンクに変更する場合はURLを更新

#### 6-2. 管理画面でのURL表示

**ファイル**: `member/admin/edit-category.php`, `member/admin/edit-content.php`, `member/admin/edit-sub.php`

```106:106:member/admin/edit-category.php
            https://the-imagine.com/<?php echo $category["category_top_img"]; ?><br>
```

**変更内容**:
- 表示用のベースURL（`https://the-imagine.com/`）を新しいドメインとパスに変更

---

### 7. バッチ処理のパス設定

**ファイル**: `member/batch/batch.php`

```1:9:member/batch/batch.php
#!/usr/local/php/7.3/bin/php
<?php
/***********************************************************/
/* 登録済みのお知らせやコンテンツを取得し、公開日を判定行って公開するスクリプト */
/***********************************************************/
require_once '/home/users/2/doinatsumi/web/the-imagine.com/member/member/scripts/BaseModel.class.php';
require_once '/home/users/2/doinatsumi/web/the-imagine.com/member/member/scripts/model/NewsModel.class.php';
require_once '/home/users/2/doinatsumi/web/the-imagine.com/member/member/scripts/model/CategoryModel.class.php';
require_once '/home/users/2/doinatsumi/web/the-imagine.com/member/member/scripts/model/ContentModel.class.php';
```

**変更内容**:
- 1行目: PHPのパスをサーバー環境に合わせて変更（`which php` コマンドで確認）
- 6-9行目: require_onceのパスを新しいプロジェクトのパスに変更（相対パスに変更することを推奨）

**推奨変更**:
```php
#!/usr/bin/php
<?php
require_once dirname(__FILE__) . '/../scripts/BaseModel.class.php';
require_once dirname(__FILE__) . '/../scripts/model/NewsModel.class.php';
require_once dirname(__FILE__) . '/../scripts/model/CategoryModel.class.php';
require_once dirname(__FILE__) . '/../scripts/model/ContentModel.class.php';
```

**バッチ実行設定**:
- サーバーのcronに登録する場合は、新しいスクリプトパスに合わせてcron設定を更新

---

### 8. Google Analytics設定

**ファイル**: `member/tmp/analytics.php`

```2:9:member/tmp/analytics.php
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-43489254-39"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('set', {'user_id': '<?php echo $session->get('member'); ?>'});

  gtag('config', 'UA-43489254-39');
```

**変更内容**:
- Google AnalyticsのトラッキングID（`UA-43489254-39`）を新しいプロジェクト用のIDに変更
- 不要な場合は、`member/index.php`、`member/login.php` などからこのファイルのインクルードを削除

---

### 9. ファイルアップロード設定

**ファイル**: `member/scripts/UploadLib.class.php`

```66:71:member/scripts/UploadLib.class.php
    private function getRootPath()
    {
      $root_path = dirname(__FILE__) . '/../contents/';
      $root_path = realpath($root_path);
      return $root_path;
    }
```

**変更内容**:
- アップロード先は相対パスで指定されているため、プロジェクト構造を維持すれば変更不要
- `/member/contents/` ディレクトリが存在し、書き込み権限（通常は755または775）が設定されていることを確認
- アップロード可能な拡張子は47行目で定義されています（png, jpg, gif, pdf, txt）

---

### 10. アップロード先ディレクトリの権限

以下のディレクトリに書き込み権限が必要です：
- `/member/contents/` とその配下の全ディレクトリ
- `/session/` ディレクトリ

---

## 確認すべきその他の設定

### データベーステーブル構造

以下のテーブルが必要です（テーブル構造の詳細は別途確認が必要）：
- `member_master` - 会員情報
- `category_master` - カテゴリー情報
- `content_master` - コンテンツ情報
- `news_master` - お知らせ情報
- `contact_master` - 問い合わせ情報
- `sub_master` - サブコンテンツ情報
- `upload_master` - アップロード情報
- `comment_master` - コメント情報
- `member_content_relation` - 会員とコンテンツの関連

既存のデータベースからエクスポートしたスキーマとデータをインポートするか、新規にテーブルを作成してください。

### PHP設定

`php.ini` またはサーバー設定で以下を確認：
- `mbstring` 拡張が有効
- `session.gc_maxlifetime` が適切に設定（現在は24時間）
- アップロードファイルサイズの制限（`upload_max_filesize`, `post_max_size`）
- `display_errors` を本番環境では `Off` に設定

### セキュリティ設定

- `.gitignore` に `/session` と `/member/contents` が含まれていることを確認（既に設定済み）
- データベースパスワードなどの機密情報がGitにコミットされないよう確認
- 管理画面のパスワードを強力なものに変更
- HTTPSの使用を推奨

---

## 変更チェックリスト

複製後の作業手順：

- [ ] 1. データベース接続設定を変更（`PdoInterface.class.php`）
- [ ] 2. 新しいデータベースを作成・インポート
- [ ] 3. 管理画面ログイン情報を変更（`admin/login.php`）
- [ ] 4. メール送信設定を全て変更（送信先、送信元、本文内のURL・アドレス）
- [ ] 5. URLパスを全て新しいパスに変更（`/` → 新しいパス）
- [ ] 6. ドメイン関連の設定を変更
- [ ] 7. バッチ処理のパスを変更・確認
- [ ] 8. Google Analytics IDを変更または削除
- [ ] 9. ファイルアップロード先ディレクトリの権限を確認
- [ ] 10. セッションディレクトリの権限を確認
- [ ] 11. ブラウザで動作確認（ログイン、コンテンツ閲覧、問い合わせ送信など）
- [ ] 12. 管理画面の動作確認（会員管理、コンテンツ登録など）
- [ ] 13. バッチ処理の動作確認

---

## 注意事項

1. **パスワードの取り扱い**: 管理画面やデータベースのパスワードは、本番環境では必ず変更してください。

2. **環境変数の使用**: 将来的には、データベース接続情報やメールアドレスなどを環境変数や設定ファイルで管理することを推奨します。

3. **エラーハンドリング**: 現在のコードでは、データベース接続エラー時にエラーメッセージが直接表示されます。本番環境では適切なエラーハンドリングを実装してください。

4. **パスの一元管理**: URLパスが多くのファイルに散在しているため、定数ファイルを作成して一元管理することを推奨します。

5. **古いパスの残存**: grep検索で見つかった全てのハードコードされたパスを確認し、必要な箇所を変更してください。

---

## 補足

### プロジェクト構造

```
imagine-2025/
├── member/              # メインアプリケーション
│   ├── admin/          # 管理画面
│   ├── batch/          # バッチ処理スクリプト
│   ├── common/         # CSS、JS、画像などの共通リソース
│   ├── contents/      # アップロードされたコンテンツ（git管理外）
│   ├── contact/        # 問い合わせ機能
│   ├── scripts/        # PHPクラス・モデル
│   │   ├── model/      # データモデル
│   │   └── ...
│   ├── tmp/            # ヘッダー・フッター・アナリティクス
│   └── ...
├── session/            # セッションファイル（git管理外）
└── docs/               # ドキュメント
```

### 主なモデルクラス

- `PdoInterface` - データベース接続管理
- `Session` - セッション管理
- `BaseModel` - モデルの基底クラス
- `MemberModel` - 会員管理
- `CategoryModel` - カテゴリー管理
- `ContentModel` - コンテンツ管理
- `NewsModel` - お知らせ管理
- `ContactModel` - 問い合わせ管理
- `UploadLib` - ファイルアップロード処理

---

## 今回の改修で実装すべき機能

### 改修の概要

既存の会員サイトを改良し、以下の機能追加・変更を行います：
- 1つの会員サイトで複数のコース（スタンダード/プレミアム、ベーシック/アドバンス）のコンテンツを出し分け
- タグ機能の追加
- TOPページでの視聴進捗表示
- コンテンツへの複数動画登録機能

既存機能の維持：
- コメント機能（現行通り）
- 質問の受付フロー（現行通り）

---

### 1. ユーザー属性によるコンテンツ出し分け機能

**現状**:
- `member_master`テーブルの`select_course`フィールドで会員のコースを管理
  - `1`: プレミアムコース（旧スタンダード）
  - その他: ベーシックコース
- 現行ではプレミアム/ベーシックのみの2コース構成

**実装内容**:
- 会員属性を拡張し、以下のコースに対応
  - **スタンダード（プレミアム）**: 過去の講座
  - **ベーシック**: 新しい講座（基本コース）
  - **アドバンス**: 新しい講座（上級コース）
- コンテンツに表示対象コースの情報を追加（例：`target_course`フィールド）
- 各会員の属性に応じて表示するコンテンツをフィルタリング
- カテゴリー一覧画面（`list.php`）、コンテンツ詳細画面（`detail.php`）、TOP画面（`index.php`）で出し分け処理を実装

**変更が必要なファイル**:
- `member_master`テーブル構造の見直し（必要に応じて）
- `content_master`テーブルに表示対象コース情報を追加
- `ContentModel.class.php` - コース別フィルタリング機能の追加
- `list.php` - カテゴリー内コンテンツ一覧の出し分け
- `detail.php` - コンテンツ詳細の閲覧権限チェック
- `index.php` - TOP画面での出し分け

---

### 2. タグ機能・タグ検索機能

**実装内容**:
- コンテンツにタグ（例：「獅子座」「金星」など）を付与できる機能
- タグによる検索機能
- タグでコンテンツを絞り込み表示できるUI

**データベース設計**:
```
新規テーブル: tag_master
- tag_id (PK)
- tag_name (VARCHAR) - タグ名（例：「獅子座」「金星」）
- created_date
- modified_date

新規テーブル: content_tag_relation
- relation_id (PK)
- content_id (FK)
- tag_id (FK)
- created_date
```

**実装ファイル**:
- `TagModel.class.php` (新規作成)
- `ContentModel.class.php` - タグとの関連付け機能追加
- `member/admin/register-content.php` - タグ選択UI追加
- `member/admin/edit-content.php` - タグ編集機能追加
- `member/search.php` (新規作成) - タグ検索画面
- 検索UIコンポーネント（サイドバーやヘッダーに配置）

---

### 3. TOPページでの視聴進捗表示機能

**現状**:
- 各カテゴリー（大テーマ）ごとの視聴進捗は、カテゴリー詳細画面（`list.php`）で表示されている
- `MemberModel::getScore()`メソッドで各カテゴリーの達成率を計算

**実装内容**:
- TOPページ（`index.php`）で、各カテゴリー（大テーマ）の視聴進捗を表示
- 未視聴のコンテンツがある場合に視覚的にわかるUI（例：進捗バー、未視聴バッジ）
- 進捗率の計算ロジックは既存の`MemberModel::getScore()`を活用

**変更が必要なファイル**:
- `member/index.php` - TOPページに視聴進捗表示を追加
- `member/common/css/main.css` - 進捗表示用のスタイル追加（必要に応じて）
- 既存の`MemberModel::getScore()`メソッドを活用

**表示イメージ**:
- 各カテゴリーのバナー画像と一緒に進捗率を表示（例：「達成率：75%」）
- 未視聴コンテンツがある場合は「NEW」や「未視聴あり」などのバッジ表示

---

### 4. 小テーマ（コンテンツ）への複数動画登録機能

**現状**:
- 1つのコンテンツ（小テーマ）に対して、動画URLは1つだけ登録可能
- `content_master`テーブルに`video_url`などのフィールドが1つだけ

**実装内容**:
- 1つのコンテンツに対して複数の動画を登録できるようにする
- 動画プレイヤーのUI改善：
  - メイン動画の下、またはサイドにサムネイルリストを表示
  - チャプター選択のように動画を切り替え可能
  - 現在視聴中の動画をハイライト表示

**データベース設計**:
```
新規テーブル: content_video
- video_id (PK)
- content_id (FK) - content_masterへの外部キー
- video_url (VARCHAR) - 動画URL
- video_title (VARCHAR) - 動画タイトル（例：「12星座について - 牡羊座編」）
- thumbnail_url (VARCHAR) - サムネイル画像URL（オプション）
- display_order (INT) - 表示順序
- created_date
- modified_date
```

**実装ファイル**:
- `ContentVideoModel.class.php` (新規作成)
- `member/admin/register-content.php` - 複数動画登録UI追加
- `member/admin/edit-content.php` - 複数動画編集機能追加
- `member/detail.php` - 複数動画表示UI実装
- `member/common/js/video-player.js` (新規作成、必要に応じて) - 動画切り替え機能
- `member/common/css/main.css` - 動画リスト表示用スタイル追加

**UI要件**:
- メイン動画エリア
- 動画リストエリア（サムネイル + タイトル）
- クリックで動画を切り替え
- 現在視聴中の動画を視覚的に識別

**注意点**:
- 既存の動画URLは移行が必要（既存の`content_master.video_url`を`content_video`テーブルに移行）
- 後方互換性を考慮した実装（既存の単一动画コンテンツも表示できるように）

---

### 5. 大テーマ（カテゴリー）構成の変更への対応

**要件**:
- 「占星術のキホン」「星とじぶん育て」などの大テーマ（カテゴリー）を変更・組み直しする予定
- 講座開始1週間前までに大テーマの構成を決定する必要がある

**対応**:
- カテゴリーの追加・編集・削除機能は既存の管理画面（`admin/register-category.php`, `admin/edit-category.php`, `admin/list-category.php`）で対応可能
- 大テーマの構成が決まった後に、コンテンツの再分類作業が必要

**確認事項**:
- 既存コンテンツとカテゴリーの関連付け（`category_id`）が適切に設定されていること
- カテゴリー画像（`category_top_img`, `category_list_img`）の準備

---

## 実装時の注意事項

### データベースマイグレーション

以下の新規テーブル作成が必要：
1. `tag_master` - タグマスター
2. `content_tag_relation` - コンテンツとタグの関連テーブル
3. `content_video` - コンテンツ動画テーブル

既存テーブルの変更：
- `content_master`テーブルに表示対象コース情報を追加（必要に応じて）

### 既存データの移行

1. **動画データの移行**:
   - 既存の`content_master`テーブルの`video_url`を`content_video`テーブルに移行するスクリプトを作成
   - 1つの動画URLを1レコードとして`content_video`に登録

2. **コース情報の設定**:
   - 既存コンテンツに対して表示対象コースを設定する作業が必要
   - バッチ処理または管理画面で一括設定可能にすること

### 後方互換性

- 既存の単一动画コンテンツも正常に表示できるように実装
- タグが付いていないコンテンツも正常に動作すること
- 既存のコメント機能、質問機能が引き続き動作すること

### パフォーマンス

- タグ検索のパフォーマンスを考慮（インデックス設定）
- 複数動画の読み込み時のパフォーマンス（遅延読み込みの検討）

---

## 改修実装チェックリスト

### フェーズ1: データベース設計・テーブル作成
- [ ] タグ関連テーブル（`tag_master`, `content_tag_relation`）の作成
- [ ] 動画テーブル（`content_video`）の作成
- [ ] 既存テーブルへのコース情報フィールド追加
- [ ] 既存データの移行スクリプト作成・実行

### フェーズ2: モデルクラスの実装
- [ ] `TagModel.class.php` の作成
- [ ] `ContentVideoModel.class.php` の作成
- [ ] `ContentModel.class.php` の拡張（タグ・動画・コース対応）
- [ ] `MemberModel.class.php` の拡張（コース別フィルタリング対応）

### フェーズ3: 管理画面の実装
- [ ] コンテンツ登録画面にタグ選択UI追加
- [ ] コンテンツ登録画面に複数動画登録UI追加
- [ ] コンテンツ編集画面にタグ・動画編集機能追加
- [ ] コンテンツ登録・編集画面にコース選択機能追加
- [ ] タグ管理画面の作成（タグの新規作成・編集・削除）

### フェーズ4: 会員画面の実装
- [ ] TOP画面に視聴進捗表示機能追加
- [ ] コンテンツ詳細画面に複数動画表示UI実装
- [ ] 動画切り替え機能（JavaScript）の実装
- [ ] タグ検索画面の作成
- [ ] コンテンツ一覧画面でのコース別フィルタリング実装
- [ ] コンテンツ詳細画面での閲覧権限チェック

### フェーズ5: UI・UXの調整
- [ ] 動画プレイヤーUIの調整（サムネイルリスト、切り替えアニメーションなど）
- [ ] 進捗表示の視覚的改善
- [ ] タグ検索UIの使いやすさ向上
- [ ] レスポンシブデザイン対応（モバイル表示の確認）

### フェーズ6: テスト・検証
- [ ] 各機能の動作テスト
- [ ] 既存機能の回帰テスト
- [ ] パフォーマンステスト
- [ ] ブラウザ互換性テスト
- [ ] 本番環境での動作確認

---

## スケジュール・マイルストーン

- **大テーマ（カテゴリー）構成の決定**: 講座開始の1週間前まで
- **データベース設計・テーブル作成**: 開発開始時
- **管理画面実装**: コンテンツ登録前に完了
- **会員画面実装**: 講座開始前に完了
- **テスト・修正**: 講座開始前に完了

