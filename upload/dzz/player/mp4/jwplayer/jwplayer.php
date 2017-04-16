<!doctype html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
 html,body{overflow:hidden;margin:0;padding:0;width:100%;height:100%;}
</style>
   <!-- flowplayer javascript component -->
   <script type="text/javascript" src="jwplayer.js"></script>
   <script>jwplayer.key="GXd6B6xGStba1d0Q/cVHlqACLeagfBItChKaEg=="</script>
</head>

<body>

   <div id="container" ></div>
<script type="text/javascript">
    jwplayer("container").setup({
        file: "<?php echo '../../../../index.php?mod=io&op=getStream&path='.urlencode(rawurlencode($_GET['path']))?>",
		image:'',
        autostart: true,
    });
</script>

</body>
</html>