<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-43489254-39"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('set', {'user_id': '<?php echo $session->get('member'); ?>'});

  gtag('config', 'UA-43489254-39');
</script>
