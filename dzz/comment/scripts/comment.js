
function feed_publish(arr,tid,targetid){
	
var html=''
	html+='<div id="comment_'+arr['cid']+'" class="itemfeed" feed-id="'+arr['cid']+'" style="display:none">';
  	html+='	<div class="left_ifed"> <a href="user.php?uid='+arr['authorid']+'" title="'+arr['author']+'" hidefocus="true">'+arr['avatar']+'</a> </div>';
  	html+='	<div class="right_ifed">';
    html+=' 	<div class="main_fed">';
    html+='  		 <div class="source_fed"> <a href="user.php?uid='+arr['authorid']+'" title="'+arr['author']+'" hidefocus="true" class="appuser_sfed skip_mmfed">'+arr['author']+'</a><span class="cont_sfed">'+__lang.talk+'：</span> </div>';
    html+='  		<div class="master_mfed"> <span class="lquote_mmfed"></span><span class="content_mmfed">'+arr['message']+'</span><span class="rquote_mmfed"></span> </div>';
	html+='	   <div class="attachment_fed">';
for(var i in arr['attachs']){
	var attach=arr['attachs'][i];
    html+='		 <div class="item_afed">';
    if(attach.type=='image'){
     html+='		 <div class="pic_fed  clearfix">';
     html+='		   <div class="img_pfed"> <a class="min_ipfed" hidefocus="true" href="javascript:;"><img src="'+attach['img']+'" data-original="'+attach['img']+'&original=1" alt="'+attach['title']+'" class=""></a> </div>';
     html+='		 </div>';
     html+='		<div class="file_fed imgfile_fed clearfix"> '+attach['title']+'<span class="kb_nffed">('+attach['filesize']+')</span>';
     html+='			<p class="down_ffed">';
	 if(attach.downloads>0){
     	html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_downAttach(\''+attach['qid']+'\')">'+__lang.download+'('+attach['downloads']+__lang.degree+')</a>';
	 }else{
	 	html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_downAttach(\''+attach['qid']+'\')">'+__lang.download+'</a>'; 
	 }
   	html+='		        <a href="javascript:void(0);" title="" hidefocus="true" class="save_dffed skip_mmfed"  onclick="feed_attach_saveto(\''+attach['qid']+'\')">'+__lang.js_saved_my_documents+'</a> ';
     html+='	        </p>';
     html+='	     </div>';
	}else if(attach.type=='video'){
	  html+='	<div class="file_fed file_fed_'+attach.type+' clearfix">';
      html+='          <div class="ico_ffed " style="margin-right:20px"><a href="javascript:;" onclick="feed_attach_preview(\''+attach['qid']+'\')"><img src="'+attach['img']+'" alt="'+attach['title']+'" class="videoclass50_50" ></a></div>';
      html+='          <p class="name_ffed">'+attach['title']+'</p>';
     html+='           <p class="down_ffed">';
	 
     if(attach.downloads>0){
     	html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_downAttach(\''+attach['qid']+'\')">'+__lang.download+'('+attach['downloads']+__lang.degree+')</a>';
	 }else{
	 	html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_downAttach(\''+attach['qid']+'\')">'+__lang.download+'</a>'; 
	 }
   	 html+='		        <a href="javascript:void(0);" title="" hidefocus="true" class="save_dffed skip_mmfed"  onclick="feed_attach_saveto(\''+attach['qid']+'\')">'+__lang.js_saved_my_documents+'</a> ';
     html+='	        </p>';
     html+='	     </div>';
}else if(attach.type=='dzzdoc' || attach.type=='link'){
      html+='	<div class="file_fed file_fed_'+attach.type+' clearfix">';
      html+='          <div class="ico_ffed "><a href="javascript:;" onclick="feed_attach_preview(\''+attach['qid']+'\')"><img src="'+attach['img']+'" alt="'+attach['title']+'" style="height:50px;"></a></div>';
      html+='          <p class="name_ffed">'+attach['title']+'</p>';
      html+='           <p class="down_ffed">';
      html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_attach_preview(\''+attach['qid']+'\')">'+__lang.preview+'</a>';
      html+='	</div>';
}else{
      html+='	<div class="file_fed file_fed_'+attach.type+' clearfix">';
      html+='          <div class="ico_ffed "><a href="javascript:;" onclick="feed_attach_preview(\''+attach['qid']+'\')"><img src="'+attach['img']+'" alt="'+attach['title']+'" style="height:50px;"></a></div>';
      html+='          <p class="name_ffed">'+attach['title']+'</p>';
     html+='           <p class="down_ffed">';
	 if(attach.preview>0){
     	html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_attach_preview(\''+attach['qid']+'\')">'+__lang.preview+'</a>';
	 }
     if(attach.downloads>0){
     	html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_downAttach(\''+attach['qid']+'\')">'+__lang.download+'('+attach['downloads']+__lang.degree+')</a>';
	 }else{
	 	html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_downAttach(\''+attach['qid']+'\')">'+__lang.download+'</a>'; 
	 }
   		 html+='		        <a href="javascript:void(0);" title="" hidefocus="true" class="save_dffed skip_mmfed"  onclick="feed_attach_saveto(\''+attach['qid']+'\')">'+__lang.js_saved_my_documents+'</a> ';
     	html+='	        </p>';
     	html+='	     </div>';
 }
   	 html+='	</div>';
         
}
    html+='	</div>';
   html+='   		<div class="action_mfed clearfix">';
   html+='     			<div class="btn_amfed">';
   html+='      	 		<ul>';
   //html+='             			<li class="more_bacfed"><a hidefocus="true" href="javascript:void(0);" onclick="feed_edit(\''+arr['cid']+'\',\''+arr['allowattach']+'\',\''+arr['allowat']+'\',\''+arr['allowsmiley']+'\')">'+__lang.edit+'</a></li>';
   html+='        	 			<li class="more_bamfed"><a hidefocus="true" href="javascript:void(0);" onclick="feed_delete(\''+arr['cid']+'\',\'comment_'+arr['cid']+'\')">'+__lang.delete+'</a></li>';
   html+='         				<li class="reply_bamfed"><a hidefocus="true" href="javascript:void(0);" onclick="getReplyForm(\''+arr['cid']+'\',\'0\',\''+arr['allowattach']+'\',\''+arr['allowat']+'\',\''+arr['allowsmiley']+'\');">'+__lang.reply+'</a></li>';
   html+='      	 		</ul>';
   html+='     			</div>';
   html+='    		   '+arr['dateline'];
   html+='  		</div>';
   html+=' 		</div>';
   html+='		<div id="comment_reply_'+arr['cid']+'" class="comment_ifed" ></div>';
   html+='	</div>';

   html+='</div>';
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
   jQuery('#message_'+tid).removeClass('writelock').removeClass('writein').val('');
   jQuery('#publish_submit_'+tid).removeAttr('disabled');
   jQuery('#message_'+tid).val(jQuery('#message_'+tid).attr('tip'));
	jQuery('#message_'+tid).css({'height':25});
	jQuery('#attachmentViewBox_'+tid).empty();
	//location.hash='#comment_'+arr['cid'];
	jQuery('#comment_'+arr['cid']+' img[data-original]').dzzthumb();
	try{
		callback_by_comment('comment_'+arr['cid'],'add');
	}catch(e){}
	
}

function feed_reply(arr){
	
 var html='';
	 html+='<div id="comment_'+arr['cid']+'" class="cmt_fed">';
     html+='   <div class="item_cfed">';
     html+='     <div class="left_icfed"> <a href="user.php?uid='+arr['authorid']+'" title="" hidefocus="true"> '+arr['avatar']+' </a> </div>';
     html+='     <div class="right_icfed">';
     html+='       <div class="master_cfed"> <a href="user.php?uid='+arr['authorid']+'" title="" class="avatar_mcfed skip_cmfed" hidefocus="true">'+arr['author']+'</a> ';
	 if(arr['rpost']){
		html+='<span class="amal_fed">'+__lang.reply+'</span>  <a href="user.php?uid='+arr['rpost']['authorid']+'" title="" class="avatar_mcfed skip_cmfed" hidefocus="true" target="_blank">'+arr['rpost']['author']+'</a>'; 
	 }
	 html+='          <span>：'+arr['message']+'</span> ';
	 html+='		</div>';
	html+='	   <div class="attachment_fed">';
	for(var i in arr['attachs']){
		var attach=arr['attachs'][i];
    html+='		 <div class="item_afed">';
    if(attach.type=='image'){
     html+='		 <div class="pic_fed clearfix">';
     html+='		   <div class="img_pfed"> <a class="min_ipfed" hidefocus="true" href="javascript:;"><img src="'+attach['img']+'" data-original="'+attach['img']+'&original=1" alt="'+attach['title']+'" class=""></a> </div>';
     html+='		 </div>';
     html+='		<div class="file_fed imgfile_fed clearfix"> '+attach['title']+'<span class="kb_nffed">('+attach['filesize']+')</span>';
     html+='			<p class="down_ffed">';
	 if(attach.downloads>0){
     	html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_downAttach(\''+attach['qid']+'\')">'+__lang.download+'('+attach['downloads']+__lang.degree+')</a>';
	 }else{
	 	html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_downAttach(\''+attach['qid']+'\')">'+__lang.download+'</a>'; 
	 }
   	 html+='		        <a href="javascript:void(0);" title="" hidefocus="true" class="save_dffed skip_mmfed"  onclick="feed_attach_saveto(\''+attach['qid']+'\')">'+__lang.js_saved_my_documents+'</a> ';
     html+='	        </p>';
     html+='	     </div>';
}else if(attach.type=='video'){
	  html+='	<div class="file_fed file_fed_'+attach.type+' clearfix">';
      html+='          <div class="ico_ffed " style="margin-right:20px"><a href="javascript:;" onclick="feed_attach_preview(\''+attach['qid']+'\')"><img src="'+attach['img']+'" alt="'+attach['title']+'" class="videoclass50_50" ></a></div>';
      html+='          <p class="name_ffed">'+attach['title']+'</p>';
      html+='           <p class="down_ffed">';
      html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_attach_preview(\''+attach['qid']+'\')">'+__lang.preview+'</a>';
   	 // html+='		        <a href="javascript:void(0);" title="" hidefocus="true" class="save_dffed skip_mmfed"  onclick="feed_attach_saveto(\''+attach['qid']+'\')">'+__lang.js_saved_my_documents+'</a> ';
     html+='	        </p>';
     html+='	     </div>';
}else if(attach.type=='dzzdoc' || attach.type=='link'){
      html+='	<div class="file_fed file_fed_'+attach.type+' clearfix">';
      html+='          <div class="ico_ffed "><a href="javascript:;" onclick="feed_attach_preview(\''+attach['qid']+'\')"><img src="'+attach['img']+'" alt="'+attach['title']+'" style="height:50px;"></a></div>';
      html+='          <p class="name_ffed">'+attach['title']+'</p>';
      html+='           <p class="down_ffed">';
      html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_attach_preview(\''+attach['qid']+'\')">'+__lang.preview+'</a>';
      html+='	</div>';
}else{
      html+='	<div class="file_fed file_fed_'+attach.type+' clearfix">';
      html+='          <div class="ico_ffed "><a href="javascript:;" onclick="feed_attach_preview(\''+attach['qid']+'\')"><img src="'+attach['img']+'" alt="'+attach['title']+'" style="height:50px;"></a></div>';
      html+='          <p class="name_ffed">'+attach['title']+'</p>';
     html+='           <p class="down_ffed">';
	 if(attach.preview>0){
     	html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_attach_preview(\''+attach['qid']+'\')">'+__lang.preview+'</a>';
	 }
     if(attach.downloads>0){
     	html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_downAttach(\''+attach['qid']+'\')">'+__lang.download+'('+attach['downloads']+__lang.degree+')</a>';
	 }else{
	 	html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_downAttach(\''+attach['qid']+'\')">'+__lang.download+'</a>'; 
	 }
   		 html+='		        <a href="javascript:void(0);" title="" hidefocus="true" class="save_dffed skip_mmfed"  onclick="feed_attach_saveto(\''+attach['qid']+'\')">'+__lang.js_saved_my_documents+'</a> ';
     	html+='	        </p>';
     	html+='	     </div>';
	 }
   	 html+='	</div>';
         
}
     html+='	</div>';
     html+='      <div class="action_cfed clearfix">';
     html+='         <div class="btn_acfed">';
     html+='           <ul>';
	 if(arr['haveperm']>0){
    // html+='             <li class="more_bacfed"><a hidefocus="true" href="javascript:void(0);" onclick="feed_edit(\''+arr['cid']+'\',\''+arr['allowattach']+'\',\''+arr['allowat']+'\',\''+arr['allowsmiley']+'\')">'+__lang.edit+'</a></li>';
	 html+='             <li class="more_bacfed"><a hidefocus="true" href="javascript:void(0);" onclick="feed_delete(\''+arr['cid']+'\',\'comment_'+arr['cid']+'\',\''+arr['pcid']+'\')">'+__lang.delete+'</a></li>';
	 }
     html+='             <li class="reply_bacfed"><a hidefocus="true" href="javascript:void(0);" onclick="getReplyForm(\''+arr['pcid']+'\',\''+arr['cid']+'\',\''+arr['allowattach']+'\',\''+arr['allowat']+'\',\''+arr['allowsmiley']+'\');">'+__lang.reply+'</a></li>';
     html+='           </ul>';
     html+='         </div>';
	 html+='       <span class="time_acfed">'+arr['dateline']+'</span> </div>';
     html+='     </div>';
     html+='   </div>';
     html+=' </div>';
   if(!document.getElementById('reply_list_'+arr['pcid'])){
	  var html1='';
	  html1+=' <div class="comment_fed" style="display: block;">';
      html1+='    <div class="corner_lfed"><span></span></div>';
      html1+='  </div> ';
	  html1+=' <div id="reply_list_'+arr['pcid']+'" class="list_cfed">'+html+'</div>';
	  jQuery('#comment_reply_'+arr['pcid']).html(html1);
   }else{
  	 jQuery('#reply_list_'+arr['pcid']).find('.cmt_fed:first').before(html);
   }
   jQuery('#comment_'+arr['cid'])
		.on('mouseenter',function(){
			jQuery(this).addClass('hover_cmt_fed');
		})
		.on('mouseleave',function(){
			jQuery(this).removeClass('hover_cmt_fed');
		});
   var replysum=parseInt(jQuery('#comment_reply_'+arr['pcid']+' .txt_cfed .num_cfed').html());
   if(replysum>0) jQuery('#comment_reply_'+arr['pcid']+' .txt_cfed .num_cfed').html(replysum+1);
   jQuery('#message_'+arr['pcid']).val('');
   jQuery('#pulish_submit_'+arr['pcid']).removeAttr('disabled');
   jQuery('#reply_publish_'+arr['pcid']).slideUp(500);
   jQuery('#attachmentViewBox_'+arr['pcid']).empty();
  //location.hash=('#comment_'+arr['pcid']);
	jQuery('#comment_'+arr['cid']+' img[data-original]').dzzthumb();
 
}
function getReplyForm(tid,pid,allowattach,allowat,allowsmiley){
	jQuery('#comment_container .itemfeed .publishsharewrap').hide();
	if(!document.getElementById('reply_publish_'+tid)){
		var el = jQuery('<div id="reply_publish_'+tid+'"></div>').appendTo('#comment_'+tid);
		ajaxget(DZZSCRIPT+'?mod=comment&op=ajax&do=getReplyForm&cid='+tid+'&allowattach='+allowattach+'&allowat='+allowat+'&allowsmiley='+allowsmiley,'reply_publish_'+tid,'reply_publish_'+tid,'','',function(){
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
			jQuery('#message_'+tid).css('height',25);
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
		jQuery('#message_'+tid).css('height',25);
	}
	
}

function feed_edit(cid,allowattach,allowat,allowsmiley){
	showWindow('publish_edit_'+cid,DZZSCRIPT+'?mod=comment&op=ajax&do=edit&cid='+cid+'&allowattach='+allowattach+'&allowat='+allowat+'&allowsmiley='+allowsmiley);
}
function feed_edit_finish(cid,allowattach,allowat,allowsmiley){
	jQuery.get(DZZSCRIPT+'?mod=comment&op=ajax&do=getcommentbycid&cid='+cid+'&allowattach='+allowattach+'&allowat='+allowat+'&allowsmiley='+allowsmiley,function(html){
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
	if(confirm(msg)){
		jQuery.getJSON(DZZSCRIPT+'?mod=comment&op=ajax&do=delete&cid='+cid,function(json){
			jQuery('#'+domid).slideUp(500,function(){
				jQuery(this).remove();
				if(tid ){
					 var replysum=parseInt(jQuery('#comment_reply_'+tid+' .txt_cfed .num_cfed').html());
					 if(replysum>0) jQuery('#comment_reply_'+tid+' .txt_cfed .num_cfed').html(replysum-1);
				}
			});
			try{
				callback_by_comment(domid,'delete');
			}catch(e){}
		});
	}
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
    html+='            <a href="javascript:void(0);" title="" class="del_fattvb" onClick="removeAttach(jQuery(this).parent().parent().parent().parent(),\''+tid+'\');" >'+__lang.del_adjunct+'</a>';
	//if(arr['aid
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
	//document.getElementById('attachmentViewBox_0').innerHTML+=html;
	
	//jQuery('#attachmentViewBox_0').html(html);
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
				showmessage(json.error,'danger',3000,1);
			}else{
				showmessage(__lang.savetosuccess+data.relativepath+json.filename,'success',3000,1);
			}
		},'json');
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
function feed_attach_del(qid){
	var url=DZZSCRIPT+'?mod=comment&op=delete&qid='+qid;
	
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
		 showWindow('openfile', 'index.php?mod=system&op=filewindow&handlekey=svaefile&mulitype=1&exts='+exts+'&callback=opencallback', 'get', '0',function(data){//只打开本地盘
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
				html+='            <a href="javascript:void(0);" title="" class="del_fattvb" onClick="removeAttach(jQuery(this).parent().parent().parent().parent(),\''+tid+'\');" >'+__lang.del_adjunct+'</a>';
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
		jQuery('#message_'+tid).addClass('writein');
	}else{
		jQuery('#publish_submit_'+tid).attr('disabled','true');
		jQuery('#message_'+tid).removeClass('writein');
	}
}





(function($) {
 
	// jQuery plugin definition
	$.fn.TextAreaExpander = function(minHeight, maxHeight) {
 
		var hCheck = !(BROWSER.ie || BROWSER.opera);
 
		// resize a textarea
		function ResizeTextarea(e) {
 
			// event or initialize element?S
			e = e.target || e;
 
			// find content length and box width
			var vlen = e.value.length, ewidth = e.offsetWidth;
			if (vlen != e.valLength || ewidth != e.boxWidth) {
 
				//if (hCheck && (vlen < e.valLength || ewidth != e.boxWidth)) e.style.height = ewidth+"px";
				var h = Math.max(e.expandMin, Math.min(e.scrollHeight, e.expandMax));
 				
				e.style.overflow = (e.scrollHeight > h ? "auto" : "hidden");
				e.style.height = h + "px";
				e.valLength = vlen;
				e.boxWidth = ewidth;
			}
 
			return true;
		};
 
		// initialize
		this.each(function() {
 
			// is a textarea?
			if (this.nodeName.toLowerCase() != "textarea") return;
			// set height restrictions
			var p = this.className.match(/expand(\d+)\-*(\d+)*/i);
			this.expandMin = minHeight || (p ? parseInt('0'+p[1], 10) : 0);
			this.expandMax = maxHeight || (p ? parseInt('0'+p[2], 10) : 99999);
 
			// initial resize
			ResizeTextarea(this);
 
			// zero vertical padding and add events
			if (!this.Initialized) {
				this.Initialized = true;
				//$(this).css("padding-top", 0).css("padding-bottom", 0);
				$(this).bind("keyup", ResizeTextarea);
			}
		});
 
		return this;
	};
 
})(jQuery);