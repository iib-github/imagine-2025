<?php
/**
 * バリデーション関数
 * @param array {array{name, value, option{is_required, type}, ・・・}
 *               type : mail / password
 * @return array 結果  {array{name, error_message}, ・・・}
 */
function validate($datum) {
  $result = array();

  foreach ($datum as $data) {
    $name = $data[0];
    $value = $data[1];
    $option = $data[2];
    $result[$name] = null;

    // 必須チェック
    if($option['is_required']) {
      if(empty($value)) {
        $result[$name] = '※入力必須項目です。';
        continue;
      }
    }

    // タイプごとのバリデーション
    if($option['type'] == 'mail') { // メアド形式チェック
      if(preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $value) == 0) {
        $result[$name] = '※メールアドレス形式でご入力ください。';
        continue;
      }
    } elseif($option['type'] == 'password') { // パスワード形式：半角英数字8文字以上
      if(preg_match("/^[a-zA-Z0-9]+$/", $value) == 0 || mb_strlen($value) < 8) {
        $result[$name] = '※8文字以上の半角英数字でご入力ください。';
        continue;
      }
    } else {
      continue;
    }

  }

  return $result;
}

?>