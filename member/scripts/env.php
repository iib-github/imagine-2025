<?php
/**
 * .envファイルから環境変数を読み込む関数
 */
function loadEnv($envPath = null) {
  if ($envPath === null) {
    $envPath = dirname(__FILE__) . '/../../.env';
  }
  
  if (!file_exists($envPath)) {
    return;
  }
  
  $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    // コメント行をスキップ
    if (strpos(trim($line), '#') === 0) {
      continue;
    }
    
    // KEY=VALUE形式をパース
    if (strpos($line, '=') !== false) {
      list($key, $value) = explode('=', $line, 2);
      $key = trim($key);
      $value = trim($value);
      
      // 既に環境変数が設定されていない場合のみ設定
      if (!isset($_ENV[$key])) {
        $_ENV[$key] = $value;
        putenv("$key=$value");
      }
    }
  }
}

/**
 * 環境変数を取得する関数
 * @param string $key 環境変数のキー
 * @param mixed $default デフォルト値
 * @return mixed 環境変数の値
 */
function env($key, $default = null) {
  $value = getenv($key);
  if ($value === false) {
    $value = isset($_ENV[$key]) ? $_ENV[$key] : $default;
  }
  return $value;
}

/**
 * デバッグモードが有効かどうかを判定
 * @return bool デバッグモードが有効な場合true
 */
function isDebugMode() {
  $debug = env('DEBUG_MODE', 'false');
  return strtolower($debug) === 'true' || $debug === '1';
}

/**
 * エラー表示設定を初期化
 */
function initializeErrorHandling() {
  $debugMode = isDebugMode();
  
  if ($debugMode) {
    // デバッグモードが有効な場合
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
  } else {
    // 本番モード
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', dirname(__FILE__) . '/../../logs/error.log');
  }
}

if (!function_exists('parsePublishDateTime')) {
  /**
   * 公開日時の文字列を DateTimeImmutable にパースする
   *
   * @param mixed $value 日付文字列
   * @return DateTimeImmutable|null パースできなかった場合はnull
   */
  function parsePublishDateTime($value) {
    if ($value === null) {
      return null;
    }

    $normalized = trim((string)$value);
    if ($normalized === '') {
      return null;
    }

    $normalized = str_replace('.', '-', $normalized);
    // datetime-local の "2024-01-01T12:00" を扱いやすくするため
    $normalized = preg_replace('/\s+/', ' ', $normalized);

    $formats = array(
      'Y-m-d H:i:s',
      'Y-m-d H:i',
      'Y-m-d',
      'Y-m-d\TH:i:s',
      'Y-m-d\TH:i',
      'Y/m/d H:i:s',
      'Y/m/d H:i',
      'Y/m/d',
    );

    foreach ($formats as $format) {
      $date = DateTimeImmutable::createFromFormat($format, $normalized);
      if ($date instanceof DateTimeImmutable) {
        return $date;
      }
    }

    try {
      return new DateTimeImmutable($normalized);
    } catch (Exception $e) {
      return null;
    }
  }
}

if (!function_exists('isPublishableNow')) {
  /**
   * 公開日時が現在時刻に到達しているかを判定する
   *
   * @param mixed $value 日付文字列
   * @param DateTimeInterface|string|null $now 判定に利用する現在時刻
   * @return bool 公開可能ならtrue
   */
  function isPublishableNow($value, $now = null) {
    $nowDate = null;
    if ($now instanceof DateTimeInterface) {
      $nowDate = ($now instanceof DateTimeImmutable)
        ? $now
        : DateTimeImmutable::createFromInterface($now);
    } elseif ($now !== null) {
      try {
        $nowDate = new DateTimeImmutable((string)$now);
      } catch (Exception $e) {
        $nowDate = new DateTimeImmutable();
      }
    } else {
      $nowDate = new DateTimeImmutable();
    }

    $publishDate = parsePublishDateTime($value);

    if ($publishDate === null) {
      return true;
    }

    return $publishDate <= $nowDate;
  }
}

