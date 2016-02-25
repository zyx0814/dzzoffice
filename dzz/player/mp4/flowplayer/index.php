<!doctype html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <style>
   /* site specific styling */
   html ,body{width:100%;height:100%;overflow:hidden;margin:0;padding:0;}
   body {
      font: 12px "Myriad Pro", "Lucida Grande", "Helvetica Neue", sans-serif;
      text-align: center;
      padding-top: 0;
      color: #999;
      background-color: #333333;
   }
</style>
   <!-- flowplayer javascript component -->
  <script type="text/javascript" src="dzz/player/mp4/flowplayer/flowplayer-3.2.12.min.js"></script>
</head>

<body><?php
$path=dzzdecode($_GET['path']);
$patharr=explode(':',$path);
if($patharr[0]=='ftp'){
	$src=$_G['siteurl'].DZZSCRIPT.'?mod=io&op=getStream&path='.rawurldecode($_GET['path']);
}else{
	$src=IO::getFileUri($path);
	$src=str_replace('-internal.aliyuncs.com','.aliyuncs.com',$src);
}
?><a href="<?php echo $src;?>" style="height:100%;width:100%;postion:absolute;left:0;top:0;overflow:hidden"  id="player"> </a> 
		<!-- this will install flowplayer inside previous A- tag. -->
		<script>
			//flowplayer("player", "flowplayer-3.2.16.swf");
   flowplayer("player", 
	{
	  // our Flash component
	  src: "./dzz/player/mp4/flowplayer/flowplayer-3.2.16.swf",
	  wmode: 'transparent'
	}
  );
</script>
</body>
</html>