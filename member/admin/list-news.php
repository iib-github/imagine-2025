<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/NewsModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('admin') === false) {
    header("Location: login.php");
    exit;
  }

  $toast_message = '';
  if (isset($_GET['status'])) {
    switch ($_GET['status']) {
      case 'created':
        $toast_message = '更新情報を登録しました。';
        break;
      case 'updated':
        $toast_message = '更新情報を更新しました。';
        break;
      case 'deleted':
        $toast_message = '更新情報を削除しました。';
        break;
      case 'toggled':
        $toast_message = '表示状態を更新しました。';
        break;
    }
  }

  // ニュース一覧取得
  $news_model = new NewsModel();
  $news_list = $news_model->select(null, array('note_date' => BaseModel::ORDER_DESC));

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>更新情報一覧 | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />
<style>
  .status-toggle-form {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
  }
  .status-toggle-label {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 22px;
    margin-right: 8px;
  }
  .status-toggle-label input {
    opacity: 0;
    width: 0;
    height: 0;
  }
  .status-toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .3s;
    border-radius: 22px;
  }
  .status-toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 2px;
    bottom: 2px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
  }
  .status-toggle-input:checked + .status-toggle-slider {
    background-color: #4CAF50;
  }
  .status-toggle-input:checked + .status-toggle-slider:before {
    transform: translateX(22px);
  }
  .status-toggle-text {
    font-size: 12px;
    font-weight: bold;
    color: #555;
  }
  .status-toggle-text.active {
    color: #2e7d32;
  }
  .status-toggle-text.inactive {
    color: #757575;
  }
  .status-toggle-header,
  .status-toggle-cell {
    text-align: center;
    width: 80px;
  }
  .btn-detail {
    display: inline-block;
    padding: 6px 12px;
    background-color: #2196F3;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    line-height: 1.4;
    transition: background-color .2s ease, transform .2s ease;
  }
  .btn-detail:hover {
    background-color: #1976D2;
    transform: translateY(-1px);
  }
</style>
</head>

<body>

<!-- Wrapper starts -->
<div id="wrapper">
  <div class="InBox"><!-- INBOX -->
    <h1>更新情報一覧</h1>

  <?php
  $menu_active = 'news';
  include_once 'menu.php';
  ?>

    <a href="edit-news.php" class="regster">
      <div class="submenu Tab">新規登録</div>
    </a>
    <table class="member">
      <tr>
        <th style="width: 30px;">ID</th>
        <th>お知らせ日時(公開日時)</th>
        <th>タイトル</th>
        <th>対象コース</th>
        <th>公開日</th>
        <th class="status-toggle-header">表示 / 非表示</th>
        <th style="width: 50px;">詳細</th>
      </tr>

      <?php foreach ($news_list as $n) : ?>
      <tr>
        <td><?php echo $n['id']; ?></td>
        <td><?php echo htmlspecialchars(mb_substr($n['note_date'], 0, 10), ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php echo htmlspecialchars($n['description'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td>
          <?php
            $target_course = !empty($n['target_course']) ? $n['target_course'] : 'all';
            switch($target_course) {
              case 'basic':
                echo 'ベーシック';
                break;
              case 'advance':
                echo 'アドバンス';
                break;
              case 'all':
              default:
                echo 'アドバンス';
                break;
            }
          ?>
        </td>
        <td><?php echo htmlspecialchars(mb_substr($n['note_date'], 0, 10), ENT_QUOTES, 'UTF-8'); ?></td>
        <td class="status-toggle-cell">
          <form class="status-toggle-form" method="POST" action="toggle-news-status.php">
            <input type="hidden" name="news_id" value="<?php echo (int)$n['id']; ?>">
            <input type="hidden" name="redirect" value="list-news.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES, 'UTF-8') : ''; ?>">
            <input type="hidden" name="target_status" value="<?php echo ((int)$n['is_active'] === 1) ? 0 : 1; ?>">
            <label class="status-toggle-label">
              <input
                type="checkbox"
                class="status-toggle-input"
                name="is_active"
                value="<?php echo ((int)$n['is_active'] === 1) ? 0 : 1; ?>"
                <?php if((int)$n['is_active'] === 1) echo ' checked="checked"'; ?>
                onchange="this.form.submit();"
              >
              <span class="status-toggle-slider"></span>
            </label>
            <span class="status-toggle-text <?php echo ((int)$n['is_active'] === 1) ? 'active' : 'inactive'; ?>">
              <?php echo ((int)$n['is_active'] === 1) ? '表示' : '非表示'; ?>
            </span>
          </form>
        </td>
        <td style="text-align:center"><button type="button" class="btn-detail" onclick="location.href='edit-news.php?n_id=<?php echo $n['id']; ?>'">詳細</button></td>
      </tr>
      <?php endforeach; ?>

    </table>
  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->

<?php if (!empty($toast_message)): ?>
<div class="toast-notice" id="toastNotice"><?php echo htmlspecialchars($toast_message, ENT_QUOTES, 'UTF-8'); ?></div>
<script>
(function(){
  var toast=document.getElementById('toastNotice');
  if(!toast)return;
  setTimeout(function(){toast.classList.add('show');},80);
  setTimeout(function(){toast.classList.remove('show');},3080);
})();
</script>
<style>
.toast-notice{position:fixed;left:20px;bottom:20px;padding:12px 20px;background:#4CAF50;color:#fff;border-radius:4px;box-shadow:0 2px 12px rgba(0,0,0,0.2);font-size:14px;opacity:0;transform:translateY(20px);transition:opacity .3s ease,transform .3s ease;z-index:9999;}
.toast-notice.show{opacity:1;transform:translateY(0);}
</style>
<?php endif; ?>

</body>
</html>