_sound={};
_sound.playurl='./dzz/sound/play.html?url=';
_sound.create=function(){
	var iframe='<div style="position:absolute;width:1px;height:1px;z-index:-9999999;overflow:hidden"><iframe id="soundframe" name="soundframe" src="about:blank" frameBorder="0" marginHeight="0" marginWidth="0" width="100%" height="100%" allowtransparency="true"></iframe></div>';
	jQuery(iframe).appendTo(document.body);
};
_sound.play=function(url){
	if(!window.frames['soundframe']){
		_sound.create();
	}
	switch(url){
		case 'msg':
			url=SITEURL+'dzz/sound/mp3/msg.mp3?t=1';
			break;
		case 'system':
			url=SITEURL+'dzz/sound/mp3/system.mp3?t=1';
			break;
		case 'shake':
			url=SITEURL+'dzz/sound/mp3/shake.mp3?t=1';
			break;
		case 'global':
			url=SITEURL+'dzz/sound/mp3/global.mp3?t=1';
			break;
		case 'tweet':
			url=SITEURL+'dzz/sound/mp3/tweet.mp3?t=1';
			break;
		case 'notice':
			url=SITEURL+'dzz/sound/mp3/tweet.mp3?t=1';
			break;
		default:
			url=SITEURL+url;
	}

	url=encodeURIComponent(url);
	window.frames['soundframe'].location=_sound.playurl+url;
};