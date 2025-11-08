<?php
  require_once dirname(__FILE__) . '/../scripts/env.php';
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/MemberModel.class.php';
  require_once dirname(__FILE__) . '/../scripts/util.php';

  // .envファイルとエラーハンドリングを初期化
  loadEnv();
  initializeErrorHandling();

  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('admin') === false) {
    header("Location: login.php");
    exit;
  }

  $menu_active = 'cstm';
  $success_count = 0;
  $skip_count = 0;
  $error_messages = array();
  $import_logs = array();

  /**
   * 購入商品の名称から会員コースを判定する
   *
   * @param string $scenario_name
   * @return int
   */
  function mapScenarioToCourse($scenario_name) {
    $scenario_name = trim($scenario_name);
    if($scenario_name === '') {
      return MemberModel::COURSE_PREMIUM;
    }

    $keyword_map = array(
      'アドバンス' => MemberModel::COURSE_PREMIUM,
      'プレミアム' => MemberModel::COURSE_PREMIUM,
      'ベーシック' => MemberModel::COURSE_BASIC,
      'セルフ' => MemberModel::COURSE_OTHER,
      'その他' => MemberModel::COURSE_OTHER,
    );

    foreach ($keyword_map as $keyword => $course_value) {
      if(mb_stripos($scenario_name, $keyword) !== false) {
        return $course_value;
      }
    }

    return MemberModel::COURSE_OTHER;
  }

  /**
   * CSVの内容をUTF-8へ変換し、テンポラリハンドルを返す
   *
   * @param string $tmp_path
   * @return resource|false
   */
  function createUtf8StreamFromCsv($tmp_path) {
    $csv_content = file_get_contents($tmp_path);
    if($csv_content === false) {
      return false;
    }

    // UTF-8のBOMを除去
    if(strncmp($csv_content, "\xEF\xBB\xBF", 3) === 0) {
      $csv_content = substr($csv_content, 3);
      $encoding = 'UTF-8';
    } else {
      $encoding = mb_detect_encoding($csv_content, array('UTF-8', 'SJIS-win', 'EUC-JP', 'ISO-2022-JP'), true);
      if($encoding === false) {
        $encoding = 'UTF-8';
      }
    }

    if($encoding !== 'UTF-8') {
      $csv_content = mb_convert_encoding($csv_content, 'UTF-8', $encoding);
    }

    $handle = fopen('php://temp', 'r+');
    if($handle === false) {
      return false;
    }

    fwrite($handle, $csv_content);
    rewind($handle);

    return $handle;
  }

  if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
      $error_messages[] = 'CSVファイルのアップロードに失敗しました。';
    } else {
      $handle = createUtf8StreamFromCsv($_FILES['csv_file']['tmp_name']);
      if($handle === false) {
        $error_messages[] = 'CSVファイルの読み込みに失敗しました。';
      } else {
        $member_model = new MemberModel();
        $header = null;
        $row_number = 0;

        while(($row = fgetcsv($handle)) !== false) {
          $row_number++;

          if($row_number === 1) {
            $header = array_map('trim', $row);
            continue;
          }

          if($header === null) {
            $error_messages[] = 'ヘッダー行が取得できませんでした。';
            break;
          }

          // 空行はスキップ
          if(count(array_filter($row, 'strlen')) === 0) {
            continue;
          }

          $mapped = array();
          foreach($header as $index => $column_name) {
            $mapped[$column_name] = isset($row[$index]) ? trim($row[$index]) : '';
          }

          $order_id = isset($mapped['注文ID']) ? $mapped['注文ID'] : '';
          $last_name = isset($mapped['姓']) ? $mapped['姓'] : '';
          $first_name = isset($mapped['名']) ? $mapped['名'] : '';
          $mail = isset($mapped['メールアドレス']) ? $mapped['メールアドレス'] : '';
          $scenario = isset($mapped['シナリオ名（購入商品）']) ? $mapped['シナリオ名（購入商品）'] : '';

          if($order_id === '' || $mail === '' || ($last_name === '' && $first_name === '')) {
            $error_messages[] = $row_number . '行目：必須項目が不足しているためスキップしました。';
            $skip_count++;
            continue;
          }

          if(!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $error_messages[] = $row_number . '行目：メールアドレスの形式が不正です。';
            $skip_count++;
            continue;
          }

          // 重複チェック
          $existing_member = $member_model->getMemberByMail($mail);
          if(!empty($existing_member)) {
            $import_logs[] = $row_number . '行目：メールアドレスが既存のためスキップしました。（' . htmlspecialchars($mail, ENT_QUOTES, 'UTF-8') . '）';
            $skip_count++;
            continue;
          }

          $member_name = trim($last_name . ' ' . $first_name);
          $select_course = mapScenarioToCourse($scenario);

          $member_data = array(
            'member_name' => $member_name,
            'select_course' => $select_course,
            'login_mail' => $mail,
            'login_password' => $order_id,
          );

          $result = $member_model->insert($member_data);
          if($result) {
            $success_count++;
          } else {
            $error_messages[] = $row_number . '行目：登録に失敗しました。';
          }
        }

        fclose($handle);
      }
    }
  }
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>会員CSVインポート | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />
<style>
  .import-result {
    margin-top: 20px;
    padding: 15px;
    border: 1px solid #ddd;
    background: #fafafa;
    border-radius: 4px;
  }
  .import-result h2 {
    font-size: 16px;
    margin-bottom: 10px;
  }
  .import-result ul {
    margin: 0;
    padding-left: 20px;
  }
  .import-result li {
    margin-bottom: 6px;
  }
  .notice-box {
    margin-top: 15px;
    padding: 12px;
    background: #f1f8ff;
    border: 1px solid #cbe0ff;
    border-radius: 4px;
    font-size: 13px;
    line-height: 1.6;
  }
  .notice-box strong {
    display: inline-block;
    margin-bottom: 6px;
  }
</style>
</head>

<body>
<!-- Wrapper starts -->
<div id="wrapper">
  <div class="InBox"><!-- INBOX -->
    <h1>会員CSVインポート</h1>

<?php
  include_once 'menu.php';
?>

    <form method="POST" action="import-members.php" enctype="multipart/form-data">
      <p><input type="submit" id="btnImport" class="Btn" value="インポート開始"></p>
      <table class="member">
        <tr>
          <th style="width:180px;">CSVファイル</th>
          <td><input type="file" name="csv_file" accept=".csv"></td>
        </tr>
      </table>
    </form>

    <div class="notice-box">
      <strong>CSVの列名と取り込み項目</strong>
      <p>・注文ID → ログインパスワード（`login_password`）</p>
      <p>・姓 / 名 → 氏名（`member_name`、スペース区切りで連結）</p>
      <p>・メールアドレス → ログインメール（`login_mail`）</p>
      <p>・シナリオ名（購入商品） → コース（`select_course`）</p>
      <p>※「アドバンス」「ベーシック」「プレミアム」を含む場合は自動判定します。それ以外は「その他」として登録されます。</p>
    </div>

<?php if($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <div class="import-result">
      <h2>取り込み結果</h2>
      <p>成功：<?php echo (int)$success_count; ?>件 / スキップ：<?php echo (int)$skip_count; ?>件</p>
      <?php if(!empty($error_messages)): ?>
      <h3>エラー</h3>
      <ul>
        <?php foreach($error_messages as $message): ?>
        <li><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>

      <?php if(!empty($import_logs)): ?>
      <h3>スキップ詳細</h3>
      <ul>
        <?php foreach($import_logs as $log): ?>
        <li><?php echo $log; ?></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>
<?php endif; ?>

  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->
</body>
</html>

