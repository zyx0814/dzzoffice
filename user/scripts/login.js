
function loginsub(formid,rspaceid){

    var url = jQuery('#'+formid).attr('action');

    url = (url)? url:'user.php?mod=login&op=logging&action=login&loginsubmit=yes';


    var formData = jQuery('#'+formid).serialize();

    var type = 'json';

    jQuery.post(url+'&returnType='+type,formData,function(json){

        if(json['success']){
			location.href=json['success']['url_forward'];
          /*  jQuery('#succeedmessage_href').href = json['success']['url_forward'];
            jQuery('#main_message').hide();
            jQuery('#main_succeed').show();
            jQuery('#succeedlocation').html(json['success']['message']);
            jQuery('#succeedmessage_href').attr('href',json['success']['url_forward']);
            setTimeout("window.location.href ='"+json['success']['url_forward']+"';", 3000);*/
        }else if(json['error']){

            jQuery('#'+rspaceid).html(json['error']);

        }else{
            jQuery('#'+rspaceid).html(__lang.system_busy);
        }
    },'json');
}
function lostpass(contid,formid,rspaceid){
    var url = jQuery('#'+formid).attr('action');

    url = (url)? url:'user.php?mod=login&op=logging&action=lostpasswd&lostpwsubmit=yes';


    var formData = jQuery('#'+formid).serialize();

    var type = 'json';

    jQuery.post(url+'&returnType='+type,formData,function(json){
		
        if(json['success']){
          var el=jQuery('#'+contid);
			console.log(el);
			var mail='http://mail.'+json['success'].email.split('@')[1];
			el.find('.Mtitle').html(__lang.password_back_email_sent_successfully);
			el.find('.Mbody').html(json['success'].msg);
			el.find('.modal-footer .toMail').on('click',function(){
				window.location.href=mail;
			})
			el.find('.modal-footer').show();
        }else if(json['error']){
			
            jQuery('#'+rspaceid).html(json['error']);

        }else{
            jQuery('#'+rspaceid).html(__lang.system_busy);
        }
    },'json');
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
