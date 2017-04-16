<!doctype html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <!-- player skin -->
   <link rel="stylesheet" type="text/css" href="dzz/player/mp4/flowplayer5/skin.css" />
   
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
   /* custom player skin */
   /*.flowplayer .fp-controls { background-color: rgba(17, 17, 17, 1)}*/
   .flowplayer .fp-timeline { background-color: rgba(204, 204, 204, 1)}
   .flowplayer .fp-progress { background-color: rgba(0, 167, 200, 1)}
   .flowplayer .fp-buffer { background-color: rgba(249, 249, 249, 1)}
   .flowplayer { background:#000}
   .fp-header{display:none}
   </style>
   <script type="text/javascript" src="static/jquery/jquery.min.js"></script>
   <!-- flowplayer javascript component -->
   <script src="dzz/player/mp4/flowplayer5/flowplayer.min.js"></script>
</head>
<body><?php 
$path=dzzdecode($_GET['path']);
$patharr=explode(':',$path);
if($patharr[0]=='ftp'){
	$src=$_G['siteurl'].DZZSCRIPT.'?mod=io&op=getStream&path='.rawurldecode($_GET['path']).'&n=play.'.$_GET['ext'];
}else{
	$src=IO::getFileUri($path);
	$src=str_replace('-internal.aliyuncs.com','.aliyuncs.com',$src);
}
if($_GET['ext']=='mp4'){
	$type='video/mp4';
}elseif($_GET['ext']=='flv'){
	$type='video/flash';
}elseif($_GET['ext']=='webm'){
	$type='video/webm';
}elseif($_GET['ext']=='ogv' || $_GET['ext']=='ogg'){
	$type='video/ogg';
}elseif($_GET['ext']=='m3u8' || $_GET['ext']=='HLS'){
	$type='application/x-mpegurl';
}
?><div class="flowplayer play-button fp-slim fp-outlined fp-mute" data-ratio="0.5625" style="height:100%;width:100%;postion:absolute;left:0;top:0;overflow:hidden">
  <video autoplay>
     <source  type="<?php echo $type ?>" src="<?php echo $src;?>">
  </video>
</div>
</body>
</html>