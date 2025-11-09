<?php
  require_once dirname(__FILE__) . '/../scripts/Session.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/UploadModel.class.php';
  require_once dirname(__FILE__) . '/../scripts/model/MemberModel.class.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('admin') === false) {
    header("Location: login.php");
    exit;
  }

  // 提出ファイル一覧取得
  $upload_model = new UploadModel();
  $upload_list = $upload_model->select(null, array('note_date' => BaseModel::ORDER_DESC));
  // 会員一覧取得
  $member_model = new MemberModel();
  $members = $member_model->select(null, array('member_id' => BaseModel::ORDER_DESC));

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title>提出ファイル一覧 | ADMIN THE Imagine</title>
<link href="common/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="common/css/style.css" rel="stylesheet" type="text/css" media="all" />
<style>
  .course-label {
    display:inline-block;
    padding:2px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:bold;
    color:#fff;
  }
  .course-label--advance { background-color:#00695c; }
  .course-label--basic { background-color:#1e88e5; }
  .course-label--all { background-color:#8e24aa; }
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
  $menu_active = 'upload';
  include_once 'menu.php';
  ?>

    <!-- <a href="edit-upload.php" class="regster">
      <div class="submenu Tab">新規登録</div>
    </a> -->
    <table class="member">
      <tr>
        <th>アップロードID</th>
        <th>日時</th>
        <th>会員名</th>
        <th>タイトル</th>
        <th>対象コース</th>
        <th style="width: 50px;">詳細</th>
        <th>ファイルリンク</th>
      </tr>

      <?php foreach ($upload_list as $n) : ?>
      <tr>
        <td><?php echo $n['upload_id']; ?></td>
        <td><?php echo htmlspecialchars(mb_substr($n['note_date'], 0, 10), ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php foreach ($members as $m) : if ($m['member_id']==$n['member_id']) { ?><?php echo htmlspecialchars($m['member_name'], ENT_QUOTES, 'UTF-8'); ?><?php } endforeach; ?></td>
        <td><?php echo htmlspecialchars($n['title'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td>
          <?php
            $target_course = !empty($n['target_course']) ? $n['target_course'] : 'advance';
            $course_name = 'アドバンス';
            $course_class = 'course-label course-label--advance';
            switch($target_course) {
              case 'basic':
                $course_name = 'ベーシック';
                $course_class = 'course-label course-label--basic';
                break;
              case 'all':
                $course_name = '全体';
                $course_class = 'course-label course-label--all';
                break;
              case 'advance':
              default:
                $course_name = 'アドバンス';
                $course_class = 'course-label course-label--advance';
                break;
            }
          ?>
          <span class="<?php echo $course_class; ?>"><?php echo htmlspecialchars($course_name, ENT_QUOTES, 'UTF-8'); ?></span>
        </td>
        <td><?php echo htmlspecialchars($n['note'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td style="text-align:center"><button type="button" class="btn-detail" onclick="location.href='../<?php echo $n['path']; ?>'">詳細</button></td>
      </tr>
      <?php endforeach; ?>

    </table>
  </div><!-- /INBOX -->
</div>
<!-- Wrapper ends -->

</body>
</html>