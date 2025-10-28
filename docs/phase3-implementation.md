# フェーズ3: 管理画面の実装

## 概要
フェーズ3では、管理画面にタグ管理機能、複数動画登録機能、コース選択機能を実装しました。

## 実装完了内容

### タスク1: タグ管理画面の作成 ✅

#### 1. タグ一覧表示（list-tag.php）
**機能:**
- 全タグ一覧を表示（タグ名でソート）
- 各タグの使用回数を表示
- 編集・削除ボタンを表示
- 新規タグ追加へのリンク

**主要機能:**
- タグの削除機能（確認ダイアログ付き）
- 成功メッセージの表示
- レスポンシブテーブルレイアウト

#### 2. タグ登録画面（register-tag.php）
**機能:**
- タグ名の入力（必須、50文字以内）
- タグの説明の入力（500文字以内）
- フォームバリデーション
  - 空欄チェック
  - 文字数制限チェック
  - 重複チェック

**エラーハンドリング:**
- 入力エラーを一覧表示
- エラー発生時もフォーム値を保持

#### 3. タグ編集画面（edit-tag.php）
**機能:**
- タグID、タグ名、説明の表示
- 各フィールドの編集
- 使用中のコンテンツ数を表示
- フォームバリデーション
  - 空欄チェック
  - 文字数制限チェック
  - 重複チェック（自身は除外）

**セキュリティ:**
- タグIDによる検証
- 存在しないタグIDの場合は一覧画面にリダイレクト

---

### タスク2: コンテンツ登録画面の拡張 ✅

#### 拡張機能1: タグ選択UI
**実装内容:**
- チェックボックスグループでタグを表示
- 複数タグの同時選択が可能
- フレックスレイアウトで見やすい配置

**コード:**
```php
<div class="checkbox-group">
  <?php
    foreach($tag_list as $tag) {
      echo '<div class="checkbox-item">';
      echo '<input type="checkbox" name="tags[]" value="' . $tag['tag_id'] . '" id="tag_' . $tag['tag_id'] . '">';
      echo '<label for="tag_' . $tag['tag_id'] . '">' . htmlspecialchars($tag['tag_name']) . '</label>';
      echo '</div>';
    }
  ?>
</div>
```

**処理:**
- 選択されたタグを配列で受け取り
- コンテンツ登録後、タグを関連付け

#### 拡張機能2: 複数動画登録UI
**実装内容:**
- 「動画を追加」ボタンで動的にフィールド追加
- 各動画に以下の入力フィールド
  - 動画タイトル
  - 動画埋め込みコード（iframe）
  - サムネイル画像URL
- 削除ボタンで動画フィールドを削除

**JavaScript機能:**
```javascript
// 動画フィールドの動的追加
function addVideoField() { /* ... */ }

// 動画フィールドの削除
function removeVideoField(btn) { /* ... */ }

// 番号の自動更新
function updateVideoNumbers() { /* ... */ }
```

**処理:**
- 複数の動画を配列で受け取り
- 表示順序を自動設定して登録
- 空のURLはスキップ

#### 拡張機能3: コース選択機能
**実装内容:**
- セレクトボックスで対象コースを選択
- 選択肢：
  - スタンダード（standard）
  - ベーシック（basic）
  - アドバンス（advance）
  - 全コース（all）

**処理:**
```php
$content_data['target_course'] = isset($_POST['target_course']) 
  ? $_POST['target_course'] 
  : ContentModel::TARGET_COURSE_ALL;
```

---

### タスク3: コンテンツ編集画面の拡張 🔗

編集画面の拡張ガイドを`edit-content-extension.md`に記載しました。

**実装内容（ガイド参照）:**

1. **タグ編集機能**
   - 既存タグの表示と編集
   - チェックボックスで選択・解除
   - タグの更新処理

2. **複数動画編集機能**
   - 既存動画の表示
   - 動画フィールドの追加・削除
   - 表示順序の自動更新

3. **コース選択機能**
   - 現在のコース選択を表示
   - セレクトボックスで変更可能

**実装手順:**
1. `edit-content-extension.md`のガイドを参照
2. 必要なRequireを追加
3. PHP処理を拡張
4. HTMLフォーム要素を追加
5. テスト実施

---

## ファイル一覧

| ファイル | ステータス | 説明 |
|---------|---------|------|
| member/admin/list-tag.php | ✅ 完成 | タグ一覧表示 |
| member/admin/register-tag.php | ✅ 完成 | タグ登録 |
| member/admin/edit-tag.php | ✅ 完成 | タグ編集 |
| member/admin/register-content.php | ✅ 拡張完成 | コンテンツ登録（タグ・動画・コース機能追加） |
| member/admin/edit-content.php | 🔗 ガイド作成 | 編集画面拡張（edit-content-extension.md参照） |

---

## 新機能の使用方法

### タグ管理の流れ
```
1. admin/list-tag.php でタグ一覧確認
   ↓
2. admin/register-tag.php で新規タグ作成
   ↓
3. admin/edit-tag.php でタグ編集
   ↓
4. list-tag.php でタグ削除
```

### コンテンツ登録の流れ
```
1. admin/register-content.php にアクセス
   ↓
2. 基本情報入力（タイトル、カテゴリ等）
   ↓
3. タグを複数選択
   ↓
4. 「動画を追加」で複数動画を登録
   ↓
5. 対象コースを選択
   ↓
6. 登録ボタンで完了
```

### コンテンツ編集の流れ
```
1. admin/edit-content.php?cont_id=XXX でアクセス
   ↓
2. 基本情報を編集
   ↓
3. タグを更新（チェックボックス）
   ↓
4. 動画を追加・編集・削除
   ↓
5. 対象コースを変更
   ↓
6. 更新ボタンで完了
```

---

## スタイルと UX

### CSS クラス一覧

| クラス名 | 説明 |
|---------|------|
| .checkbox-group | チェックボックスグループコンテナ |
| .checkbox-item | 個別チェックボックスアイテム |
| .video-entry | 動画エントリーボックス |
| .add-video-btn | 動画追加ボタン |
| .remove-video-btn | 動画削除ボタン |
| .error-list | エラーメッセージ一覧 |
| .message | メッセージボックス |
| .info-box | 情報ボックス |

### JavaScript 関数一覧

| 関数名 | 説明 |
|-------|------|
| addVideoField() | 新規動画フィールドを追加 |
| removeVideoField(btn) | 動画フィールドを削除 |
| updateVideoNumbers() | 動画の番号を更新 |

---

## データベース連携

### 使用するテーブル
- `content_master` - コンテンツ情報（target_course フィールド使用）
- `tag_master` - タグマスター
- `content_tag_relation` - コンテンツとタグの関連
- `content_video` - コンテンツの動画

### 使用するモデルクラス
- `ContentModel` - コンテンツ操作
- `TagModel` - タグ操作
- `ContentVideoModel` - 動画操作
- `CategoryModel` - カテゴリー操作

---

## セキュリティ機能

1. **認証チェック**
   - セッション認証で管理者のみアクセス可能
   - ログイン画面へのリダイレクト

2. **入力バリデーション**
   - 必須フィールドのチェック
   - 文字数制限の確認
   - 重複チェック

3. **XSS対策**
   - htmlspecialchars() で出力をエスケープ
   - ENT_QUOTES フラグで全て対象

4. **CSRF対策**
   - POST メソッドの使用
   - 削除には確認ダイアログを表示

---

## 次ステップ（フェーズ4以降）

### フェーズ4: 会員画面の実装
- TOP画面に視聴進捗表示
- 複数動画の表示UI実装
- タグ検索画面の作成
- コース別フィルタリング実装

### フェーズ5: UI・UXの調整
- 動画プレイヤーUIの調整
- 進捗表示の視覚的改善
- レスポンシブデザイン対応

### フェーズ6: テスト・検証
- 各機能の動作テスト
- ブラウザ互換性テスト
- 本番環境での動作確認

---

## トラブルシューティング

### Q: タグが表示されない
A: タグ_masterテーブルのデータ確認、TagModel::getTagList() の実行確認

### Q: 動画が登録されない
A: content_videoテーブルの確認、ContentVideoModel のバリデーション確認

### Q: コース選択が反映されない
A: content_master の target_course フィールドの存在確認

---

## 実装確認チェックリスト

- [ ] list-tag.php で全タグが表示される
- [ ] register-tag.php でタグ新規作成ができる
- [ ] edit-tag.php でタグ編集ができる
- [ ] タグ削除で確認ダイアログが表示される
- [ ] register-content.php でタグを複数選択できる
- [ ] 動画を追加・削除できる
- [ ] コース選択ができる
- [ ] コンテンツ登録時にすべての情報が保存される
- [ ] edit-content.php が正常に拡張される
- [ ] エラーメッセージが正確に表示される
