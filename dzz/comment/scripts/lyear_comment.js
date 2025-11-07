
function feed_publish(arr,tid,targetid){
var html=''
	html+='<li id="comment_'+arr['cid']+'" class="itemfeed" feed-id="'+arr['cid']+'" style="display:none">';
  	html+='	<div class="d-flex lyear-message-item"> <a href="user.php?uid='+arr['authorid']+'" class="img-avatar-48" title="'+arr['author']+'" hidefocus="true" target="_blank">'+arr['avatar']+'</a>';
  	html+='	<div class="flex-grow-1 ms-2">';
    html+=' <a href="user.php?uid='+arr['authorid']+'" title="'+arr['author']+'" class="text-break" hidefocus="true" target="_blank"><strong>'+arr['author']+'</strong></a>';
    html+=' <div class="text-muted">'+arr['dateline']+' '+arr['xtllq']+' '+arr['ip']+'<span class="float-end"><a class="dcolor" hidefocus="true" class="dcolor" href="javascript:void(0);" onclick="feed_delete(\''+arr['cid']+'\',\'comment_'+arr['cid']+'\')"><i class="mdi mdi-delete"></i>&nbsp;'+__lang.delete+'</a>'+' '+'<a class="dcolor" hidefocus="true" class="dcolor" href="javascript:void(0);" onclick="getReplyForm(\''+arr['cid']+'\',\'0\',\''+arr['allowattach']+'\',\''+arr['allowat']+'\',\''+arr['allowsmiley']+'\');"><i class="mdi mdi-reply"></i>&nbsp;'+__lang.reply+'</a></span></div><p class="text-break">'+arr['message']+'</p>';
	html+='<div class="row">';
for(var i in arr['attachs']){
	var attach=arr['attachs'][i];
    html+='<div class="col-xs-12 col-sm-6 col-lg-4">';
	html+='<div class="attachoffer">';
	html+='<div class="ms-2 me-auto  p-2">';
	html+='<div class="fw-bold"><a class="img-avatar-48" hidefocus="true" href="javascript:;"><img src="'+attach['img']+'" data-original="'+attach['img']+'" alt="'+attach['title']+'" class=""></a>'+attach['title'];
	if(attach['filesize']){
		html+='<span>('+attach['filesize']+')</span>';
	}
	html+='</div><div class="p-2">';
	if(attach.preview>0){
		html+='<a href="javascript:;" title="" hidefocus="true" class="btn btn-outline-info btn-sm" onclick="feed_attach_preview(\''+attach['qid']+'\')">'+__lang.preview+'</a>';
	}
	if(attach.type=='dzzdoc' || attach.type=='link'){
	} else {
		html+='<a href="javascript:;" title="" hidefocus="true" class="btn btn-outline-info btn-sm" onclick="feed_downAttach(\''+attach['qid']+'\')">'+__lang.download;
		if(attach.downloads>0){
			html+='('+attach['downloads']+__lang.degree+')';
		}
		html+='</a>';
		html+='<a href="javascript:void(0);" title="" hidefocus="true" class="btn btn-outline-info btn-sm" onclick="feed_attach_saveto(\''+attach['qid']+'\')">'+__lang.js_saved_my_documents+'</a> ';
	}
	html+='</div>';
	html+='</div>';
	html+='</div>';
	html+='</div>';
}
html+='	</div>';
   html+='		<ul id="comment_reply_'+arr['cid']+'" class="list-unstyled" ></ul>';
   html+='	</div>';
   html+='	</div>';
   html+='</li>';
   if(targetid && jQuery('#'+targetid).length){
	    var el=jQuery('#'+targetid+' > .itemfeed:first'); 
		 if(el.length>0) el.before(html);
  		 else jQuery('#'+targetid).html(html);
   }else{
  	  var el=jQuery('#comment_container > .itemfeed:first');
	    if(el.length>0) el.before(html);
  		else jQuery('#comment_container').html(html);
   }
   jQuery('#comment_'+arr['cid']).slideDown(500);
   jQuery('#publish_submit_'+tid).removeAttr('disabled');
   jQuery('#message_'+tid).val(jQuery('#message_'+tid).attr('tip'));
	jQuery('#attachmentViewBox_'+tid).empty();
	jQuery('#comment_'+arr['cid']+' img[data-original]').dzzthumb();
	try{
		callback_by_comment('comment_'+arr['cid'],'add');
	}catch(e){}
}

function feed_reply(arr){
 var html='';
	 html+='<li id="comment_'+arr['cid']+'" class="cmt_fed">';
     html+='   <div class="d-flex lyear-message-item">';
     html+='   <a href="user.php?uid='+arr['authorid']+'" class="img-avatar-48" title="" hidefocus="true" target="_blank"> '+arr['avatar']+' </a>';
     html+='     <div class="flex-grow-1 ms-2">';
     html+='       <a href="user.php?uid='+arr['authorid']+'" title="" class="avatar_mcfed text-break" hidefocus="true" target="_blank"><strong>'+arr['author']+'</strong></a>';
	 if(arr['rpost']){
		html+='<span class="text-muted m-1">'+__lang.reply+'</span><a href="user.php?uid='+arr['rpost']['authorid']+'" title="" class="avatar_mcfed" hidefocus="true" target="_blank"><strong>'+arr['rpost']['author']+'</strong></a>'; 
	 }
	 html+='<div class="text-muted">'+arr['dateline']+' '+arr['xtllq']+' '+arr['ip'];
	 html+='<span class="float-end">';
	 if(arr['haveperm']>0){
		html+='<a class="dcolor" hidefocus="true" class="dcolor" href="javascript:void(0);" onclick="feed_delete(\''+arr['cid']+'\',\'comment_'+arr['cid']+'\',\''+arr['pcid']+'\')"><i class="mdi mdi-delete"></i>&nbsp;'+__lang.delete+'</a>'+' ';
	}
	html+='<a class="dcolor" hidefocus="true" class="dcolor" href="javascript:void(0);" onclick="getReplyForm(\''+arr['pcid']+'\',\''+arr['cid']+'\',\''+arr['allowattach']+'\',\''+arr['allowat']+'\',\''+arr['allowsmiley']+'\');"><i class="mdi mdi-reply"></i>&nbsp;'+__lang.reply+'</a>';
	html+='</span></div><p class="text-break">'+arr['message']+'</p>';
	if(arr['attachs']){
		html+='<div class="row">';
	for(var i in arr['attachs']){
		var attach=arr['attachs'][i];
		html+='<div class="col-xs-12 col-sm-6 col-lg-4">';
		html+='<div class="attachoffer">';
		html+='<div class="ms-2 me-auto  p-2">';
		html+='<div class="fw-bold"><a class="img-avatar-48" hidefocus="true" href="javascript:;"><img src="'+attach['img']+'" data-original="'+attach['img']+'" alt="'+attach['title']+'" class=""></a>'+attach['title'];
		if(attach['filesize']){
			html+='<span>('+attach['filesize']+')</span>';
		}
		html+='</div><div class="p-2">';
		if(attach.preview>0){
			html+='<a href="javascript:;" title="" hidefocus="true" class="btn btn-outline-info btn-sm" onclick="feed_attach_preview(\''+attach['qid']+'\')">'+__lang.preview+'</a>';
		}
		if(attach.type=='dzzdoc' || attach.type=='link'){

		} else {
			html+='<a href="javascript:;" title="" hidefocus="true" class="btn btn-outline-info btn-sm" onclick="feed_downAttach(\''+attach['qid']+'\')">'+__lang.download;
			if(attach.downloads>0){
				html+='('+attach['downloads']+__lang.degree+')';
			}
			html+='</a>';
			html+='<a href="javascript:void(0);" title="" hidefocus="true" class="btn btn-outline-info btn-sm" onclick="feed_attach_saveto(\''+attach['qid']+'\')">'+__lang.js_saved_my_documents+'</a> ';
		}
		html+='</div>';
		html+='</div>';
		html+='</div>';
		html+='</div>';
	}
	html+='	</div>';
	}
     html+='     </div>';
     html+='   </div>';
     html+=' </li>';
   if(!document.getElementById('reply_list_'+arr['pcid'])){
	  var html1='';
	  html1+=' <div id="reply_list_'+arr['pcid']+'">'+html+'</div>';
	  jQuery('#comment_reply_'+arr['pcid']).html(html1);
   }else{
  	 jQuery('#reply_list_'+arr['pcid']).find('.cmt_fed:first').before(html);
   }
   var replysum=parseInt(jQuery('#comment_reply_'+arr['pcid']+' .txt_cfed .num_cfed').html());
   if(replysum>0) jQuery('#comment_reply_'+arr['pcid']+' .txt_cfed .num_cfed').html(replysum+1);
   jQuery('#message_'+arr['pcid']).val('');
   jQuery('#pulish_submit_'+arr['pcid']).removeAttr('disabled');
   jQuery('#reply_publish_'+arr['pcid']).slideUp(500);
   jQuery('#attachmentViewBox_'+arr['pcid']).empty();
   jQuery('#comment_'+arr['cid']+' img[data-original]').dzzthumb();
 
}
function getReplyForm(tid,pid,allowattach,allowat,allowsmiley){
	jQuery('#comment_container .publishsharewrap').hide();
	if(!document.getElementById('reply_publish_'+tid)){
		var el = jQuery('<div id="reply_publish_'+tid+'"></div>').appendTo('#comment_'+tid);
		ajaxget(DZZSCRIPT+'?mod=comment&op=ajax&do=getReplyForm&template=1&cid='+tid+'&allowattach='+allowattach+'&allowat='+allowat+'&allowsmiley='+allowsmiley,'reply_publish_'+tid,'reply_publish_'+tid,'','',function(){
			 var el=jQuery('#reply_who_'+tid);
			 if(pid>0){
				 el.find('.toname_wcpsw').html(jQuery('#comment_'+pid+' .avatar_mcfed').html());
				 el.show();
				 jQuery('#reply_pid_'+tid).val(pid);
			}else{
				el.hide(); 
				jQuery('#reply_pid_'+tid).val('0');
			}
			jQuery('#comment_'+tid+' textarea[name="message"]').val('').focus();
		});
	}else{ 
		var el=jQuery('#reply_who_'+tid);
		if(pid>0){
				 el.find('.toname_wcpsw').html(jQuery('#comment_'+pid+' .avatar_mcfed').html());
				 el.show();
				 jQuery('#reply_pid_'+tid).val(pid);
		}else{
				el.hide(); 
				jQuery('#reply_pid_'+tid).val('0');
			}
		jQuery('#reply_publish_'+tid).find('.publishsharewrap').show().end().slideDown(500);
		jQuery('#comment_'+tid+' textarea[name="message"]').val('').focus();
	}
	
}

function feed_edit(cid,allowattach,allowat,allowsmiley){
	showWindow('publish_edit_'+cid,DZZSCRIPT+'?mod=comment&op=ajax&do=edit&template=1&cid='+cid+'&allowattach='+allowattach+'&allowat='+allowat+'&allowsmiley='+allowsmiley,'get',0);
}
function feed_edit_finish(cid,allowattach,allowat,allowsmiley){
	jQuery.get(DZZSCRIPT+'?mod=comment&op=ajax&do=getcommentbycid&template=1&cid='+cid+'&allowattach='+allowattach+'&allowat='+allowat+'&allowsmiley='+allowsmiley,function(html){
		jQuery('#comment_'+cid).replaceWith(html);
		
	});
}
function feed_delete(cid,domid,tid){
	var msg='';
	if(tid){
		msg=__lang.sure_want_delete_comment;
	}else{
		msg=__lang.sure_want_delete_all_comment;
	}
	showDialog(msg, 'confirm','', function(){		
		jQuery.getJSON(DZZSCRIPT+'?mod=comment&op=ajax&do=delete&cid='+cid,function(json){
			jQuery('#'+domid).slideUp(500,function(){
				if(json.msg=='success') {
					showmessage('删除成功', 'success', '3000', 1);
				}
				jQuery(this).remove();
				if(tid){
					 var replysum=parseInt(jQuery('#comment_reply_'+tid+' .txt_cfed .num_cfed').html());
					 if(replysum>0) jQuery('#comment_reply_'+tid+' .txt_cfed .num_cfed').html(replysum-1);
				}
			});
			try{
				callback_by_comment(domid,'delete');
			}catch(e){}
		}).fail(function(jqXHR, textStatus, errorThrown) {
			showmessage('{lang do_failed}' + textStatus, 'danger', 3000, 1);
		});
	});
}
function feed_addAttach(arr,el,tid){
	var html='';
	html+=' <div  class="attachment_previewer">';
    html+='     <div class="attachmentviewbox">';
    html+='         <div class="view_attvb clearfix">';
	if(arr['isimage']){
	html+='           <div class="ico_vattvb "><a href="'+arr['img']+'" target="_blank"><img alt="'+arr['filename']+'" src="'+arr['img']+'" class="img_50_50"></a></div>';
	}else{
	html+='           <div class="ico_vattvb "><img class="img_50_50" alt="'+arr['filename']+'" src="'+arr['img']+'"></div>';
	}
  	html+='  		  <div class="ico_vattvb_right">';
    html+='            <div class="ico_name">'+arr['filename']+'</div>';
    html+='            <a href="javascript:void(0);" title="" class="del_fattvb btn btn-outline-danger btn-sm" onClick="removeAttach(jQuery(this).parent().parent().parent().parent(),\''+tid+'\');" >'+__lang.del_adjunct+'</a>';
    html+='            <input type="hidden" name="attach[aid][]" value="'+arr['aid']+'" />';
	html+='            <input type="hidden" name="attach[title][]" value="'+arr['filename']+'" />';
	html+='            <input type="hidden" name="attach[type][]" value="attach" />';
	html+='            <input type="hidden" name="attach[img][]" value="" />';
	html+='            <input type="hidden" name="attach[url][]" value="" />';
	html+='          </div>';
    html+='          </div>';
    html+='      </div>';
    html+=' </div>';
	
	el.replaceWith(html);
	check_attach_share_tid(tid);
}
function feed_downAttach(qid){
	var url=DZZSCRIPT+'?mod=comment&op=down&qid='+qid;
	if(BROWSER.ie){
			window.open(url);
		}else{
			if(!window.frames['hidefram']) jQuery('<iframe id="hideframe" name="hideframe" src="about:blank" frameborder="0" marginheight="0" marginwidth="0" width="0" height="0" allowtransparency="true" style="display:none;z-index:-99999"></iframe>').appendTo('body');
			window.frames['hideframe'].location=url;
		}
}
function feed_attach_saveto(qid){
	var url=DZZSCRIPT+'?mod=comment&op=saveto&qid='+qid;
	showWindow('saveto','index.php?mod=system&op=filewindow&type=2','get','0',function(fid,data){
		jQuery.post(url,{fid:fid},function(json){
			if(json.error){
				showmessage(json.error,'danger','3000',1);
			}else{
				showmessage(__lang.savetosuccess+data.relativepath+json.filename,'success','3000',1);
			}
		},'json').fail(function (jqXHR, textStatus, errorThrown) {
            showmessage(__lang.do_failed, 'error', 3000, 1);
        });
	});
}
function feed_attach_preview(qid){
	var url=DZZSCRIPT+'?mod=comment&op=preview&qid='+qid;
	if(!top._config) window.open(url);
	else{
		if(!window.frames['hidefram']) jQuery('<iframe id="hideframe" name="hideframe" src="about:blank" frameborder="0" marginheight="0" marginwidth="0" width="0" height="0" allowtransparency="true" style="display:none;z-index:-99999"></iframe>').appendTo('body');
		window.frames['hideframe'].location=url;
	}
}

function check_attach_share_tid(tid){
	if(!tid) tid='0';
	var sum=jQuery('#attachmentViewBox_'+tid).find('.attachment_previewer').length; 
	var val=jQuery('#message_'+tid).val();
	var reg=/^__lang.share_the(\d+)__lang.js_a_file/ig;
	if(sum<1){
		if(val!='') jQuery('#message_'+tid).val(val.replace(reg,''));
	}else{
		if(val=='' || val==jQuery('#message_'+tid).attr('tip')){
			jQuery('#message_'+tid).val(__lang.share_the+sum+__lang.js_a_file);
		}else{
			jQuery('#message_'+tid).val(val.replace(reg,__lang.share_the+sum+__lang.js_a_file));
		}
	}
	check_publish_enable(tid);
	jQuery('#message_'+tid).focus().caret('pos',document.getElementById('message_'+tid).value.length);
	return sum;
}
function removeAttach(el,tid){
	el.slideUp(500,function(){jQuery(this).remove();check_attach_share_tid(tid);});
		
}

//从桌面选择文件
function uploadfrom_desktop(tid){
	if(!tid) tid='0';
	try{
		var openexts = {
			  attach:[__lang.typename_attach,["ATTACH","IMAGE","DOCUMENT","VIDEO","LINK","DZZDOC"],""],
			  image:[__lang.typename_image+"(*.jpg,*.jpeg,*.png,*.gif)",["IMAGE","JPG","JPEG","PNG","GIF"],""]
		};
		var exts=JSON.stringify(openexts);
		 exts = exts.replace(/\"/g,'&quot;');
		exts = exts.replace(/\(/g,'|');
		exts = exts.replace(/\)/g,'$');
		exts = encodeURIComponent(exts);
		showWindow('openfile', 'index.php?mod=system&op=filewindow&template=1&handlekey=svaefile&mulitype=1&exts='+exts+'&callback=opencallback', 'get', '0',function(data){//只打开本地盘
		var datas=data;
		for(var i in datas){
			var arr=datas[i];
			var html='';
				html+=' <div id="attachment_previewer_ico_'+arr['icoid']+'" class="attachment_previewer">';
				html+='     <div class="attachmentviewbox">';
				html+='         <div class="view_attvb clearfix">';
				html+='           <div class="ico_vattvb "><img alt="'+arr['name']+'" src="'+arr['img']+'" class="img_50_50"></div>';
			  	html+='  		  <div class="ico_vattvb_right">';
				html+='            <div class="ico_name">'+arr['name']+'</div>';
				html+='            <a href="javascript:void(0);" title="" class="del_fattvb btn btn-outline-danger btn-sm" onClick="removeAttach(jQuery(this).parent().parent().parent().parent(),\''+tid+'\');" >'+__lang.del_adjunct+'</a>';
				if(arr['type']=='image' || arr['type']=='attach' || arr['type']=='document'){
				html+='            <input type="hidden" name="attach[aid][]" value="'+arr['aid']+'" />';
				html+='            <input type="hidden" name="attach[img][]" value="" />';
				html+='            <input type="hidden" name="attach[type][]" value="attach" />';
				html+='            <input type="hidden" name="attach[url][]" value="" />';
				}else{
				html+='            <input type="hidden" name="attach[aid][]" value="0" />';
				html+='            <input type="hidden" name="attach[type][]" value="'+arr['type']+'" />';
				html+='            <input type="hidden" name="attach[img][]" value="'+arr['img']+'" />';
				html+='            <input type="hidden" name="attach[url][]" value="'+arr['url']+'" />';
				}
				html+='            <input type="hidden" name="attach[title][]" value="'+arr['name']+'" />';
				html+='            <input type="hidden" name="attach[ext][]" value="'+(arr['ext']?arr['ext']:'')+'" />';
				
				html+='          </div>';
				html+='        </div>';
				html+='      </div>';
				html+=' </div>';
				jQuery('#attachmentViewBox_'+tid).append(html);
				check_attach_share_tid(tid);
		}
	}); 
	}catch(e){
		
	}
}

function check_publish_enable(tid){
	//统计字数
	if(!tid) tid='0';
	var str=document.getElementById('message_'+tid).value.replace(/[\r\n]/i,'');
	var length=mb_strlen(str);
	if(length>1000){
		length=1000-length;
	}
	document.getElementById('num_input_'+tid).innerHTML=length;
	if(length>0 && length<1000){
		jQuery('#publish_submit_'+tid).removeAttr('disabled','true');
		jQuery('#publish_submit_'+tid).removeClass('disabled');
	}else{
		jQuery('#publish_submit_'+tid).attr('disabled','true');
		jQuery('#publish_submit_'+tid).addClass('disabled');
	}
}