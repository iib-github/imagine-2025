<?php
  //一旦非表示
  header("Location: /membership/member/");
  exit;

  require_once dirname(__FILE__) . '/scripts/Session.class.php';
  require_once dirname(__FILE__) . '/scripts/model/MemberModel.class.php';
  require_once dirname(__FILE__) . '/scripts/model/UploadModel.class.php';
  require_once dirname(__FILE__) . '/scripts/validate.php';
  $session = Session::getInstance();

  // セッションがなければログイン画面に遷移させる。
  if($session->get('member') === false) {
    header("Location: login.php");
    exit;
  }

  // メンバー情報取得
  $upload_model = new UploadModel();

  //表示数
  $list_num = '';
  // アップロード情報取得
  $upload_list = $upload_model->UploadList($list_num,$session->get('member'));

  if($_SERVER["REQUEST_METHOD"] == "POST") {
    // $tempfile = $_FILES['file']['tmp_name'];
    // $filename = './contents/works/' . $_FILES['file']['name'];

    // 登録情報
    $upload_data = array(
      'member_id'=>$_POST['mid'],
      'title' => $_POST['title'],
      'note' => $_POST['note'],
      // 'path' => $filename,
    );

    $success = $upload_model->registerUploadWork($upload_data);
    if($success) {
      header("Location: upload-complete.php");
      exit;
    }
  }

?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" >
<title>ワークの提出 - THE Imagine Membersサイト</title>
<meta name="robots" content="noindex,nofollow" />
<meta name="viewport" content="width=device-width">
<link rel="apple-touch-icon" href="/membership/member/common/img/apple-touch-icon.png">
<link href="common/css/main.css" rel="stylesheet">
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<script src="common/js/respond.min.js"></script>
<![endif]-->
<!--[if IE 6]><script src="common/js/minmax.js"></script><![endif]-->
<?php include 'tmp/analytics.php';?>
</head>

<body>
<section id="MV">
  <div class="Cnt">
    <h1><img src="common/img/login_logo.png" width="320" alt="THE Imagine"/></h1>
  </div>
</section>
<div id="wrapper">
  <!-- Wrapper -->
  <div id="Contents">
    <!-- Contents -->
    <div id="Main">
      <!-- Main -->
      <section id="Document">
        <h2>ワークの提出</h2>
        <div class="Block">
          <p class="midNote">レッスン課題の提出はこちらよりお願いします。</p>
          <form class="MyAccount" method="POST" action="upload.php" enctype="multipart/form-data">
            <input type="hidden" name="mid" value="<?php echo $session->get('member'); ?>">
            <dl>
              <dt>タイトル　<span class="red"><sup>*</sup>必須</span></dt>
              <dd><input type="text" name="title" value="" required></dd>
            </dl>
            <dl>
              <dt>ファイルの詳細　<span class="red"><sup>*</sup>必須</span></dt>
              <dd><textarea name="note" rows="4" required></textarea></dd>
            </dl>
            <dl style="margin-bottom: 60px;">
              <dt>ファイルのアップロード（提出可能ファイル：.jpg/.png/.gif/.pdf/.txt）　<span class="red"><sup>*</sup>必須</span></dt>
              <dd>
                <div class="uploadButton">
                    ファイルを選択
                    <input type="file" name="file" id="file" onchange="uv.style.display='block'; uv.value = this.value;" required />
                    <input type="email" id="uv" class="uploadValue" disabled />
                </div>
            </dl>
            <div class="EntBtn"><input type="submit" value="送信する"></div>
          </form>
        </div>
      </section>
<?php if (!empty($upload_list)) { ?>
      <section id="List">
        <h2>提出リスト</h2>
        <div class="Block">
          <p>今まで提出したファイルはこちらです。</p>
          <ul class="UploadList">
          <?php foreach ($upload_list as $s) { ?>
            <li>
              <dl>
                <dt><?php echo htmlspecialchars(mb_substr($s['note_date'], 0, 10), ENT_QUOTES, 'UTF-8'); ?></dt>
                <dd><?php echo $s['title']; ?></dd>
                <dd class="last"><a href="<?php echo $s['path']; ?>" target="_blank" class="btnFile">確認</a></dd>
              </dl>
            </li>
          <?php } ?>
          </ul>
        </div>
      </section>
<?php } ?>
    </div>
    <!-- /Main -->
    <div id="Side">
      <?php include 'sidebar.php'; ?>
    </div>
  </div>
  <!-- /Contents -->
<?php include 'tmp/footer.php';?>
</div>
<!-- /Wrapper -->
<script src="common/js/smoothscroll.js"></script>
</body>
</html>