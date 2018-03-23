
if(document.getElementById('imgbg')){
	jQuery.getScript('static/js/imgReady.js',function(){
		imgReady(document.getElementById('imgbg').src, function () {
			setImage(this.width,this.height);
		});
	});
}
function setImage(width,height){
	var clientWidth=document.documentElement.clientWidth;
	var clientHeight=document.documentElement.clientHeight;
	var r0=clientWidth/clientHeight;
	var r1=width/height;
	if(r0>r1){//width充满
		w=clientWidth;
		h=w*(height/width);
	}else{
		h=clientHeight;
		w=h*(width/height);
	}
	if(document.getElementById('imgbg')){
      document.getElementById('imgbg').style.width=w+'px';
      document.getElementById('imgbg').style.height=h+'px';
    }
}
