<!doctype html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <!-- player skin -->
   <link rel="stylesheet" type="text/css" href="minimalist.css" />
   
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
   .flowplayer { width: 100%; background-color: #222; background-size: cover;  }
   .flowplayer .fp-controls { background-color: rgba(17, 17, 17, 1)}
   .flowplayer .fp-timeline { background-color: rgba(204, 204, 204, 1)}
   .flowplayer .fp-progress { background-color: rgba(0, 167, 200, 1)}
   .flowplayer .fp-buffer { background-color: rgba(249, 249, 249, 1)}
   .flowplayer { background:#000}

   </style>

   <!-- flowplayer depends on jQuery 1.7.1+ -->
   <script type="text/javascript" src="../../../scripts/jquery-1.10.2.min.js"></script>
<!--   <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
-->
   <!-- flowplayer javascript component -->
   <script src="flowplayer.min.js"></script>

</head>

<body>
<?php 

$src='../../../../index.php?mod=io&op=getStream&path='.urlencode(rawurldecode($_GET['path']));

if($_GET['ext']=='mp4'){
	$type='video/mp4';
}elseif($_GET['ext']=='flv'){
	$type='video/flash';
}elseif($_GET['ext']=='webm'){
	$type='video/webm';
}elseif($_GET['ext']=='ogv'){
	$type='video/ogg';
}elseif($_GET['ext']=='m3u8'){
	$type='application/x-mpegurl';
}
?>

   <div data-swf="flowplayer.swf" class="flowplayer play-button" data-ratio="0.5625" style="height:100%;width:100%;postion:absolute;left:0;top:0;overflow:hidden">
      <video autoplay>
         <source  type="<?php echo $type ?>" src="<?php echo $src;?>">
      </video>
      
   </div>

</body>