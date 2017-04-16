function feed_vote_switch(tid){
	if(!tid) tid=0
	var votestatus=jQuery('#votestatus_'+tid).val();
	if(votestatus>0){
		jQuery('#vote_post_container_'+tid).hide();
		jQuery('#votestatus_'+tid).val('0');
	}else{
		
		jQuery('#vote_post_container_'+tid).show();
		jQuery('#votestatus_'+tid).val('1')
	}
}
function getNewThread (feedType){
	jQuery.get(DZZSCRIPT+'?mod=feed&op=ajax&do=getNewThreads&t='+timestamp,function(json){
		 var el=jQuery('#feed_container > .itemfeed:first');
		   if(el.length>0) el.before(json);
		   else jQuery('#feed_container').html(json);
		jQuery('#feed_notes').hide();
	});
}
function feed_publish(arr,flag){
	jQuery('#feed_tid_'+arr['tid']).remove();
	var html=''
	html+='<div id="feed_tid_'+arr['tid']+'" class="itemfeed" feed-id="'+arr['tid']+'" style="display:none">';
  	html+='	<div class="left_ifed"> <a href="user.php?uid='+arr['authorid']+'" title="'+arr['author']+'" hidefocus="true"><img width="50" height="50" src="avatar.php?uid='+arr['authorid']+'" alt="'+arr['author']+'"></a> </div>';
  	html+='	<div class="right_ifed">';
    html+=' 	<div class="main_fed">';
	if(arr['readperm']>0){
		html+='     	 <span class="lockline_fed">'+__lang.visible_only_look+'</span>';
	}else{
		html+='     	 <span class="openline_fed">'+__lang.all_visible+'</span>';
	}
    html+='  		 <div class="source_fed"> <a href="user.php?uid='+arr['authorid']+'" title="'+arr['author']+'" hidefocus="true" class="name_sfed skip_mmfed">'+arr['author']+'</a><span class="cont_sfed">'+__lang.talk+'：</span> </div>';
	if(arr['message']!=''){
    html+='  		<div class="master_mfed"> <span class="lquote_mmfed"></span><span class="content_mmfed">'+arr['message']+'</span><span class="rquote_mmfed"></span> </div>';
	}
	html+='			<div class="attachment_fed">';
for(var i in arr['attachs']){
	var attach=arr['attachs'][i];
    html+='		 <div class="item_afed">';
    if(attach.type=='image'){
     html+='		 <div class="pic_fed  clearfix">';
     html+='		   <div class="img_pfed"> <a class="min_ipfed" hidefocus="true" href="'+attach['url']+'" rel="'+attach['url']+'"  target="_blank"><img src="'+attach['img']+'" alt="'+attach['title']+'" class="artZoom"  style="cursor: url(dzz/feed/images/zoomin.cur), pointer;" ></a> </div>';
     html+='		 </div>';
     html+='		<div class="file_fed imgfile_fed clearfix"> '+attach['title']+'<span class="kb_nffed">('+attach['filesize']+')</span>';
     html+='			<p class="down_ffed">';
	 if(attach.downloads>0){
     	html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_downAttach(\''+attach['qid']+'\')">'+__lang.download+'('+attach['downloads']+__lang.degree+')</a>';
	 }else{
	 	html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_downAttach(\''+attach['qid']+'\')">'+__lang.download+'</a>'; 
	 }
   	 html+='		        <a href="javascript:void(0);" title="" hidefocus="true" class="save_dffed skip_mmfed"  onclick="feed_attach_saveto(\''+attach['qid']+'\')">'+__lang.js_saved_my_documents+'</a> ';
	 html+='		        <a href="javascript:void(0);" title="" hidefocus="true" class="del_dffed skip_mmfed"  onclick="feed_attach_delete('+attach['qid']+',this)">'+__lang.delete+'</a> ';
     html+='	        </p>';
     html+='	     </div>';
	}else if(attach.type=='video'){
	  html+='	<div class="file_fed file_fed_'+attach.type+' clearfix">';
      html+='          <div class="ico_ffed " style="margin-right:20px"><a href="javascript:;" onclick="feed_attach_preview(\''+attach['qid']+'\')"><img src="'+attach['img']+'" alt="'+attach['title']+'" class="videoclass50_50" ></a></div>';
      html+='          <p class="name_ffed">'+attach['title']+'</p>';
     html+='           <p class="down_ffed">';
	  html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_attach_preview(\''+attach['qid']+'\')">'+__lang.preview+'</a>';
	  html+='		        <a href="javascript:void(0);" title="" hidefocus="true" class="del_dffed skip_mmfed"  onclick="feed_attach_delete('+attach['qid']+',this)">'+__lang.delete+'</a> ';
     html+='	        </p>';
     html+='	     </div>';
}else if(attach.type=='dzzdoc' || attach.type=='link'){
      html+='	<div class="file_fed file_fed_'+attach.type+' clearfix">';
      html+='          <div class="ico_ffed "><a href="javascript:;" onclick="feed_attach_preview(\''+attach['qid']+'\')"><img src="'+attach['img']+'" alt="'+attach['title']+'" style="max-height:50px;"></a></div>';
      html+='          <p class="name_ffed">'+attach['title']+'</p>';
      html+='           <p class="down_ffed">';
      html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_attach_preview(\''+attach['qid']+'\')">'+__lang.preview+'</a>';
	  html+='		        <a href="javascript:void(0);" title="" hidefocus="true" class="del_dffed skip_mmfed"  onclick="feed_attach_delete('+attach['qid']+',this)">'+__lang.delete+'</a> ';
      html+='	</div>';
}else{
      html+='	<div class="file_fed file_fed_'+attach.type+' clearfix">';
      html+='          <div class="ico_ffed "><a href="javascript:;" onclick="feed_attach_preview(\''+attach['qid']+'\')"><img src="'+attach['img']+'" alt="'+attach['title']+'" style="max-height:50px;"></a></div>';
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
		 html+='		        <a href="javascript:void(0);" title="" hidefocus="true" class="del_dffed skip_mmfed"  onclick="feed_attach_delete('+attach['qid']+',this)">'+__lang.delete+'</a> ';
     	html+='	        </p>';
     	html+='	     </div>';
 }
   	 html+='	</div>';
         
}
 if(arr['votestatus']>0){ 
	html+='<div id="vote_container_'+arr['tid']+'" class="item_afed vote-container">';
	html+='	</div>';
  } 
	html+='			  </div>';
    html+='   		<div class="action_mfed clearfix">';
    html+='     			<div class="btn_amfed">';
    html+='      	 		<ul>';
    html+='        	 			<li class="more_bamfed"><a hidefocus="true" href="javascript:void(0);" onclick="feed_edit(\''+arr['pid']+'\',\'feed_tid_'+arr['tid']+'\')">'+__lang.edit+'</a></li>';
   html+='        	 			<li class="more_bamfed"><a hidefocus="true" href="javascript:void(0);" onclick="feed_delete(\''+arr['pid']+'\',\'feed_tid_'+arr['tid']+'\')">'+__lang.delete+'</a></li>';
  // html+='         				<li class="notice_bamfed"><a class="notice_setting" href="javascript:void(0)" data="{&quot;objid&quot;:&quot;237802_200444511_1_1_635186733527126642&quot;,&quot;objType&quot;:&quot;4&quot;}">提醒我</a></li>';
   html+='        		 		<li class="_bb_collect"> <a hidefocus="true" href="javascript:void(0);" iscollected="false">'+__lang.collect+'</a> </li>';
   html+='         				<li class="reply_bamfed"><a hidefocus="true" href="javascript:void(0);" onclick="feed_publish_reply(\''+arr['tid']+'\');">'+__lang.reply+'</a></li>';
   html+='      	 		</ul>';
   html+='     			</div>';
   html+='    		   '+arr['dateline'];
   html+='  		</div>';
   html+=' 		</div>';
   html+='	</div>';
   html+='	<div id="comment_'+arr['tid']+'" class="comment_ifed" >';
   html+='	</div>';
   html+='</div>';
   var el=jQuery('#feed_container > .itemfeed:first');
   if(el.length>0) el.before(html);
   else jQuery('#feed_container').html(html);
   jQuery('#feed_tid_'+arr['tid']).slideDown(500);
   if(jQuery('#vote_container_'+arr['tid']).length){
	    ajaxget(DZZSCRIPT+'?mod=dzzvote&op=ajax&do=getvote&id='+arr['tid']+'&idtype=feed','vote_container_'+arr['tid'],'vote_container_'+arr['tid']);
   }
   jQuery('#message_0').removeClass('writelock').val('');
   $('publish_submit_0').className='btn btn-small';
   $('attachmentViewBox_0').innerHTML='';
   jQuery('#message_0').css('background','url(dzz/feed/images/sendsuccess.png) no-repeat center center transparent');
   if(arr['votestatus']>0){ 
    ajaxget(DZZSCRIPT+'?mod=dzzvote&op=ajax&do=getvotepost&id=&idtype=feed','vote_container_body_0','vote_container_body_0');
    $('votestatus_0').value=0; 
   }
   $('vote_post_container_0').style.display='none';
   window.setTimeout(function(){jQuery('#message_0').css('background','none');},3000);
}

function feed_reply(arr){
	
 var html='';
	 html+='<div id="reply_'+arr['pid']+'" class="cmt_fed">';
     html+='   <div class="item_cfed">';
     html+='     <div class="left_icfed"> <a href="user.php?uid='+arr['authorid']+'" title="" hidefocus="true"> <img src="avatar.php?uid='+arr['authorid']+'&size=small" alt="'+arr['author']+'" width="30" height="30"> </a> </div>';
     html+='     <div class="right_icfed">';
     html+='       <div class="master_cfed"> <a href="user.php?uid='+arr['authorid']+'" title="" class="avatar_mcfed skip_cmfed" hidefocus="true">'+arr['author']+'</a> ';
	 if(arr['rpost']){
		html+='<span class="amal_fed">'+__lang.reply+'</span>  <a href="user.php?uid='+arr['rpost']['authorid']+'" title="" class="avatar_mcfed skip_cmfed" hidefocus="true" target="_blank">'+arr['rpost']['author']+'</a>'; 
	 }
	 html+='          <span>：'+arr['message']+'</span> ';
	 html+='		</div>';
for(var i in arr['attachs']){
		var attach=arr['attachs'][i];
    html+='		 <div class="item_afed">';
    if(attach.type=='image'){
     html+='		 <div class="pic_fed clearfix">';
     html+='		   <div class="img_pfed"> <a class="min_ipfed" hidefocus="true" href="'+attach['url']+'" rel="'+attach['url']+'"  target="_blank"><img src="'+attach['img']+'" alt="'+attach['title']+'" class="artZoom"  style="cursor: url(dzz/feed/images/zoomin.cur), pointer;" ></a> </div>';
     html+='		 </div>';
     html+='		<div class="file_fed imgfile_fed clearfix"> '+attach['title']+'<span class="kb_nffed">('+attach['filesize']+')</span>';
     html+='			<p class="down_ffed">';
	 if(attach.downloads>0){
     	html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_downAttach(\''+attach['qid']+'\')">'+__lang.download+'('+attach['downloads']+__lang.degree+')</a>';
	 }else{
	 	html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_downAttach(\''+attach['qid']+'\')">'+__lang.download+'</a>'; 
	 }
   	 html+='		        <a href="javascript:void(0);" title="" hidefocus="true" class="save_dffed skip_mmfed"  onclick="feed_attach_saveto(\''+attach['qid']+'\')">'+__lang.js_saved_my_documents+'</a> ';
	 html+='		        <a href="javascript:void(0);" title="" hidefocus="true" class="del_dffed skip_mmfed"  onclick="feed_attach_delete('+attach['qid']+',this)">'+__lang.delete+'</a> ';
     html+='	        </p>';
     html+='	     </div>';
}else if(attach.type=='video'){
	  html+='	<div class="file_fed file_fed_'+attach.type+' clearfix">';
      html+='          <div class="ico_ffed " style="margin-right:20px"><a href="javascript:;" onclick="feed_attach_preview(\''+attach['qid']+'\')"><img src="'+attach['img']+'" alt="'+attach['title']+'" class="videoclass50_50" ></a></div>';
      html+='          <p class="name_ffed">'+attach['title']+'</p>';
     html+='           <p class="down_ffed">';
	 
      html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_attach_preview(\''+attach['qid']+'\')">'+__lang.preview+'</a>';
	  html+='		        <a href="javascript:void(0);" title="" hidefocus="true" class="del_dffed skip_mmfed"  onclick="feed_attach_delete('+attach['qid']+',this)">'+__lang.delete+'</a> ';
     html+='	        </p>';
     html+='	     </div>';
}else if(attach.type=='dzzdoc' || attach.type=='link'){
      html+='	<div class="file_fed file_fed_'+attach.type+' clearfix">';
      html+='          <div class="ico_ffed "><a href="javascript:;" onclick="feed_attach_preview(\''+attach['qid']+'\')"><img src="'+attach['img']+'" alt="'+attach['title']+'" style="max-height:50px;"></a></div>';
      html+='          <p class="name_ffed">'+attach['title']+'</p>';
      html+='           <p class="down_ffed">';
      html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_attach_preview(\''+attach['qid']+'\')">'+__lang.preview+'</a>';
	  html+='		        <a href="javascript:void(0);" title="" hidefocus="true" class="del_dffed skip_mmfed"  onclick="feed_attach_delete('+attach['qid']+',this)">'+__lang.delete+'</a> ';
      html+='	</div>';
}else{
      html+='	<div class="file_fed file_fed_'+attach.type+' clearfix">';
      html+='          <div class="ico_ffed "><a href="javascript:;" onclick="feed_attach_preview(\''+attach['qid']+'\')"><img src="'+attach['img']+'" alt="'+attach['title']+'" style="max-height:50px;"></a></div>';
      html+='          <p class="name_ffed">'+attach['title']+'</p>';
     html+='           <p class="down_ffed">';
	 if(attach.preview>0){
     	html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_attach_preview(\''+attach['qid']+'\')"'+__lang.preview+'</a>';
	 }
     if(attach.downloads>0){
     	html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_downAttach(\''+attach['qid']+'\')">'+__lang.download+'('+attach['downloads']+__lang.degree+')</a>';
	 }else{
	 	html+='	 			<a href="javascript:;" title="" hidefocus="true" class="btn_dffed skip_mmfed"  onclick="feed_downAttach(\''+attach['qid']+'\')">'+__lang.download+'</a>'; 
	 }
   		 html+='		        <a href="javascript:void(0);" title="" hidefocus="true" class="save_dffed skip_mmfed"  onclick="feed_attach_saveto(\''+attach['qid']+'\')">'+__lang.js_saved_my_documents+'</a> ';
		 html+='		        <a href="javascript:void(0);" title="" hidefocus="true" class="del_dffed skip_mmfed"  onclick="feed_attach_delete('+attach['qid']+',this)">'+__lang.delete+'</a> ';
     	html+='	        </p>';
     	html+='	     </div>';
	 }
   	 html+='	</div>';
         
}
     html+='      <div class="action_cfed clearfix">';
     html+='         <div class="btn_acfed">';
     html+='           <ul>';
	 if(arr['haveperm']>0){
	 html+='             <li class="more_bacfed"><a hidefocus="true" href="javascript:void(0);" onclick="feed_edit(\''+arr['pid']+'\',\'reply_'+arr['pid']+'\',\''+arr['tid']+'\')">'+__lang.edit+'</a></li>';
     html+='             <li class="more_bacfed"><a hidefocus="true" href="javascript:void(0);" onclick="feed_delete(\''+arr['pid']+'\',\'reply_'+arr['pid']+'\',\''+arr['tid']+'\')">'+__lang.delete+'</a></li>';
	 }
     html+='             <li class="reply_bacfed"><a hidefocus="true" href="javascript:void(0);" onclick="feed_publish_reply(\''+arr['tid']+'\',\''+arr['pid']+'\');">'+__lang.reply+'</a></li>';
     html+='           </ul>';
     html+='         </div>';
	 if(arr['readperm']<0){
	 html+='       <span class="lock_fed" original-title="">'+__lang.secret+'</span>';
     }
	 html+='       <span class="time_acfed">'+arr['dateline']+'</span> </div>';
     html+='     </div>';
     html+='   </div>';
     html+=' </div>';
   if(!document.getElementById('reply_list_'+arr['tid'])){
	  var html1='';
	  html1+=' <div class="comment_fed" style="display: block;">';
      html1+='    <div class="corner_lfed"><span></span></div>';
      html1+='  </div> ';
	  html+=' <div id="reply_list_'+arr['tid']+'" class="list_cfed"></div>';
	  jQuery('#comment_'+arr['tid']).html(html1+html);
   }else{
  	 jQuery(html).appendTo('#reply_list_'+arr['tid']);
   }
   jQuery('#reply_'+arr['pid'])
		.on('mouseenter',function(){
			jQuery(this).addClass('hover_cmt_fed');
		})
		.on('mouseleave',function(){
			jQuery(this).removeClass('hover_cmt_fed');
		});
  var replysum=parseInt(jQuery('#comment_'+arr['tid']+' .txt_cfed .num_cfed').html());
	if(replysum>0) jQuery('#comment_'+arr['tid']+' .txt_cfed .num_cfed').html(replysum+1);
   jQuery('#message_'+arr['tid']).val('');
   $('publish_submit_'+arr['tid']).className='btn btn-small';
   $('attachmentViewBox_'+arr['tid']).innerHTML='';
	jQuery('#publish_'+arr['tid']).slideUp(500);
}
function feed_publish_reply(tid,pid){
	jQuery('.itemfeed .publishsharewrap').hide();
	if(!document.getElementById('reply_publish_'+tid)){
		jQuery('<div id="reply_publish_'+tid+'"></div>').appendTo('#feed_tid_'+tid);
		ajaxget(ajaxurl+'&do=getReplyForm&tid='+tid,'reply_publish_'+tid,'reply_publish_'+tid,'','',function(){
			 var el=jQuery('#reply_who_'+tid);
			 if(pid>0){
				 el.find('.toname_wcpsw').html(jQuery('#reply_'+pid+' .avatar_mcfed').html());
				 el.show();
				 jQuery('#reply_pid_'+tid).val(pid);
			}else{
				el.hide(); 
				jQuery('#reply_pid_'+tid).val('0');
			}
		});
	}else{ 
		var el=jQuery('#reply_who_'+tid);
		if(pid>0){
				
				 el.find('.toname_wcpsw').html(jQuery('#reply_'+pid+' .avatar_mcfed').html());
				 el.show();
				 jQuery('#reply_pid_'+tid).val(pid);
		}else{
				el.hide(); 
				jQuery('#reply_pid_'+tid).val('0');
			}
		jQuery('#publish_'+tid).slideDown(500);
	}
}
function feed_addAttach(arr,el,tid){
		var html='';
	html+=' <div  class="attachment_previewer">';
    html+='     <div class="attachmentviewbox">';
    html+='         <div class="view_attvb clearfix">';
if(arr['isimage']){
	html+='           <div class="ico_vattvb "><a href="'+arr['img']+'" target="_blank"><img alt="'+arr['filename']+'" src="'+arr['img']+'" class="img_50_50"></a></div>';
	html+='            <input type="hidden" name="attach[type][]" value="image" />';
}else{
	html+='           <div class="ico_vattvb "><img class="img_50_50" alt="'+arr['filename']+'" src="'+arr['img']+'"></div>';
	html+='            <input type="hidden" name="attach[type][]" value="attach" />';
}
  	html+='  		  <div class="ico_vattvb_right">';
    html+='            <div class="ico_name">'+arr['filename']+'</div>';
    html+='            <a href="javascript:void(0);" title="" class="del_fattvb" onClick="removeAttach(jQuery(this).parent().parent().parent().parent(),\''+tid+'\');" >'+__lang.del_adjunct+'</a>';
	//if(arr['aid
    html+='            <input type="hidden" name="attach[aid][]" value="'+arr['aid']+'" />';
	html+='            <input type="hidden" name="attach[title][]" value="'+arr['filename']+'" />';
	
	html+='            <input type="hidden" name="attach[img][]" value="" />';
	html+='            <input type="hidden" name="attach[url][]" value="" />';
	html+='          </div>';
    html+='          </div>';
    html+='      </div>';
    html+=' </div>';
	
	el.replaceWith(html);
	check_attach_share_tid(tid);
	//$('attachmentViewBox_0').innerHTML+=html;
	
	//jQuery('#attachmentViewBox_0').html(html);
}
function feed_downAttach(qid){
	var url=DZZSCRIPT+'?mod=feed&op=down&qid='+qid;
	if(BROWSER.ie){
			window.open(url);
		}else{
			window.frames['hideframe'].location=url;
		}
}
function feed_attach_saveto(qid){
	var url=DZZSCRIPT+'?mod=feed&op=saveto&qid='+qid;
	window.frames['hideframe'].location=url;
	
}
function feed_attach_delete(qid,obj){
	if(confirm(__lang.sure_want_delete_attachment)){
		jQuery.getJSON(DZZSCRIPT+'?mod=feed&op=ajax&do=attachdel&qid='+qid,function(json){
			if(json.error) showmessage(json.error,'danger',3000,1);
			else if(json.msg=='success'){
				jQuery(obj).closest('.item_afed').slideUp();
			}
		});
	}
}
function feed_attach_preview(qid){
	var url=DZZSCRIPT+'?mod=feed&op=preview&qid='+qid;
	if(!top._config){
		window.open(url);
	}else{
	if(!window.frames['hidefram']) jQuery('<iframe id="hideframe" name="hideframe" src="about:blank" frameborder="0" marginheight="0" marginwidth="0" width="0" height="0" allowtransparency="true" style="display:none;z-index:-99999"></iframe>').appendTo('body');
		window.frames['hideframe'].location=url;
	}
}
function feed_edit(pid){
	showWindow('publish_edit_'+pid,ajaxurl+'?&do=edit&pid='+pid);
}
function feed_edit_finish(data){
	jQuery.get(ajaxurl+'&do=getfeedbypid&pid='+data.pid,function(html){
		if(data.first>0){
			jQuery('#feed_tid_'+data.tid).replaceWith(html);
			 jQuery('#feed_tid_'+data.tid)
				.on('mouseenter',function(){
					jQuery(this).addClass('hover_itemfeed');
				})
				.on('mouseleave',function(){
					jQuery(this).removeClass('hover_itemfeed');
				});
		}else{
			jQuery('#reply_'+data.pid).replaceWith(html);
			
			 jQuery('#reply_'+data.pid)
				.on('mouseenter',function(){
					jQuery(this).addClass('hover_cmt_fed');
				})
				.on('mouseleave',function(){
					jQuery(this).removeClass('hover_cmt_fed');
				});
		}
	 	
	});
}
function feed_delete(pid,domid,tid){
	if(tid){
		var msg=__lang.sure_want_delete_reply;
	}else{
		var msg=__lang.all_delete_messages;
	}
	if(confirm(msg)){
		jQuery.getJSON(ajaxurl+'&do=delete&pid='+pid,function(json){
			jQuery('#'+domid).slideUp(500,function(){
				jQuery(this).remove();
				if(tid ){
					 var replysum=parseInt(jQuery('#comment_'+tid+' .txt_cfed .num_cfed').html());
					 if(replysum>0) jQuery('#comment_'+tid+' .txt_cfed .num_cfed').html(replysum-1);
				}
			});
		});
	}
}

function check_publish_enable(tid){
	//统计字数
	if(!tid) tid='0';
	var str=$('message_'+tid).value.replace(/[\r\n]/i,'');
	var length=mb_strlen(str);
	if(length>1000){
		length=1000-length;
	}
	$('num_input_'+tid).innerHTML=length;
	if(length>0 && length<1000){
		$('publish_submit_'+tid).className='btn btn-small btn-primary';
		jQuery('#message_'+tid).addClass('writein');
	}else{
		$('publish_submit_'+tid).className='btn btn-small';
		jQuery('#message_'+tid).removeClass('writein');
	}
}
function check_attach_share_tid(tid){
	if(!tid) tid='0';
	var sum=jQuery('#attachmentViewBox_'+tid).find('.attachment_previewer').length; 
	var val=jQuery('#message_'+tid).val();
	var reg=/^__lang.share_the(\d+)__lang.js_a_file}/ig;
	if(sum<1){
		jQuery('#message_'+tid).val(val.replace(reg,''));
	}else{
		if(val==''){
			jQuery('#message_'+tid).val(__lang.share_the+sum+__lang.js_a_file);
		}else{
			jQuery('#message_'+tid).val(val.replace(reg,__lang.share_the+sum+__lang.js_a_file));
		}
	}
	check_publish_enable(tid);
	jQuery('#message_'+tid).focus().caret('pos',$('message_'+tid).value.length);
	return sum;
}
function removeAttach(el,tid){
	el.slideUp(500,function(){jQuery(this).remove();check_attach_share_tid(tid);});
		
}

//从桌面选择文件
function uploadfrom_desktop(tid){
	if(!tid) tid='0';
	parent.OpenFile('open',__lang.open_file,{attach:[__lang.typename_attach,['ATTACH','IMAGE','DOCUMENT','VIDEO','LINK','DZZDOC'],''],image:[_lang.type_image+'(*.jpg,*.jpeg,*.png,*.gif)',['IMAGE','JPG','JPEG','PNG','GIF'],'']},{bz:'',multiple:true},function(data){//只打开本地盘
		var datas=[];
		if(data.params.multiple){
			datas=data.icodata
		}else{
			datas=[data.icodata];
		}
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
}
(function($) {
 
	// jQuery plugin definition
	$.fn.TextAreaExpander = function(minHeight, maxHeight) {
 
		var hCheck = !(BROWSER.ie || BROWSER.opera);
 
		// resize a textarea
		function ResizeTextarea(e) {
 
			// event or initialize element?
			e = e.target || e;
 
			// find content length and box width
			var vlen = e.value.length, ewidth = e.offsetWidth;
			if (vlen != e.valLength || ewidth != e.boxWidth) {
 
				if (hCheck && (vlen < e.valLength || ewidth != e.boxWidth)) e.style.height = "0px";
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
			//ResizeTextarea(this);
 
			// zero vertical padding and add events
			if (!this.Initialized) {
				this.Initialized = true;
				$(this).css("padding-top", 0).css("padding-bottom", 0);
				$(this).bind("keyup", ResizeTextarea);
			}
		});
 
		return this;
	};
 
})(jQuery);

function checkInDom(obj,id){
	if(!obj) return false;
	if(obj.id==id) return true;
	else if(obj.tagName=='BODY'){
		return false;
	}else{
		return checkInDom(obj.parentNode,id);
	}
};
function fixTree_organization(el){
	el.find('.tree-heng1').each(function(){
		var tr=jQuery(this).parent().parent().parent();
		var dep=jQuery(this).parent().find('.tree-su').length;
		
		tr.nextAll().each(function(){
			var child_org=jQuery(this).find('.child-org');
			var dep1=child_org.find('.tree-su').length;
			if(dep1<=dep) return false;
			else{
				child_org.find('.tree-su').eq(dep).removeClass('tree-su');
			}
		});
		
	});
}
function selDepart(obj){
	var el=jQuery(obj);
	var orgid=jQuery(obj).attr('_orgid');
	var tid=el.parent().parent().attr('tid');
	var orgname=el.find('.child-org').html().replace(/<span.+?<\/span>/ig,'');
	document.getElementById('message_'+tid).value+='@['+orgname+':gid_'+orgid+'] ';
	jQuery('#message_'+tid).caret('pos',document.getElementById('message_'+tid).value);
	jQuery('#message_'+tid).trigger('keyup.atwho');	
	jQuery('#at_department_'+tid+'_menu').hide();
}
