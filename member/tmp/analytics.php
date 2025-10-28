<?php
  // env.phpが読み込まれていない場合は読み込む
  if (!function_exists('env')) {
    require_once dirname(__FILE__) . '/../scripts/env.php';
    loadEnv();
  }
  
  $ga_tracking_id = env('GA_TRACKING_ID', 'UA-43489254-39');
  
  // トラッキングIDが空の場合はAnalyticsを出力しない（開発環境など）
  if (!empty($ga_tracking_id)):
?>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo htmlspecialchars($ga_tracking_id, ENT_QUOTES, 'UTF-8'); ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('set', {'user_id': '<?php echo $session->get('member'); ?>'});

  gtag('config', '<?php echo htmlspecialchars($ga_tracking_id, ENT_QUOTES, 'UTF-8'); ?>');
</script>
<?php endif; ?>
