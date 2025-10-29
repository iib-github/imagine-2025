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

