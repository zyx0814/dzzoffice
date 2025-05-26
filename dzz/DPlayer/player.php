<style>
    html, body, dplayer {
        overflow: hidden;
        width: 100%;
        height: 100%;
        margin: 0;
    }
</style>
<div id="dplayer"></div>
<script src="images/flv.min.js"></script>
<script src="images/dash.all.min.js"></script>
<script src="images/DPlayer.min.js"></script>
<script src="images/hls.min.js"></script>
<script src="images/dash.all.min.js"></script>
<script src="images/webtorrent.min.js"></script>
<script>
    const dp = new DPlayer({
        container: document.getElementById('dplayer'),
        screenshot: true,
        video: {
            url: '<?php echo urldecode($_GET['url']) ?>',
            type: '',
            customType: {
                shakaDash: function (video, player) {
                    var src = video.src;
                    var playerShaka = new shaka.Player(video); // 将会修改 video.src
                    playerShaka.load(src);
                },
            },
        },
    });
</script>