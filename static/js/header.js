var _header = {};
_header.init = function(formhash){
	_header.formhash=formhash;
	/*if(jQuery('.bs-left-container').length<1){
		jQuery('.resNav .leftTopmenu').hide();
	}*/
};

_header.loging_close = function(){
	var msg ='<span style="font-size:1.28rem">'+ __lang.js_exit+'</span>';
	if(msg){		
		showDialog( msg, 'confirm','', function(){		
		jQuery.get('user.php?mod=login&op=logging&action=logout&formhash='+_header.formhash+'&t='+new Date().getTime(),function(data){
			window.location.reload();
		});
	});
	}
};
_header.leftTopmenu=function(obj,flag){
	var clientWidth=document.documentElement.clientWidth;
	if(!flag){
		if(jQuery('.bs-left-container').is(':visible')){
			flag='hide';
		}else{
			flag='show';
		}
	}
	if(flag==='hide'){
		jQuery('.bs-left-container').hide();
		jQuery('.left-drager').hide();
		jQuery('.bs-main-container').css('marginLeft',0);
		jQuery(obj).removeClass('leftOpen');
	}else if(flag==='show'){
		jQuery('.bs-left-container').show();
		var leftWidth=jQuery('.bs-left-container').outerWidth(true);
		if(leftWidth<20){
			leftWidth=20;
			jQuery('.bs-left-container').width(leftWidth);
			jQuery('.left-drager').css({'left':leftWidth,'cursor':'w-resize'});
		}
		jQuery('.left-drager').show();
		jQuery('.bs-main-container').css('marginLeft',clientWidth<768?0:leftWidth);
		jQuery(obj).addClass('leftOpen');
	}
};
//头像颜色随机取出
/*_header.Topcolor=function(){
	var colors=['#6b69d6','#a966ef','#e9308d','#e74856','#f35b42','#00cc6a','#0078d7','#5290f3','#00b7c3','#0099bc','#018574','#c77c52','#ff8c00','#68768a','#7083cb','#26a255'];
	var num = parseInt(Math.random()*10);
	jQuery('#Topcarousel').css({'background-color':colors[num]});
}*/
