/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
function checkAdminLogin(str){
	if(str.match(/id=\"loginform\"/i)){
		return true;
	}else{
		return false;
	}
}
function show_guide(){
	jQuery('#orguser_container').load(ajaxurl+'do=guide',function(){
		location.hash='';
	});
}
function delDepart(obj){
	jQuery(obj).parent().parent().remove();
}
var tpml_index=0;
function addorgsel(){
	jQuery('#selorg_container').append(' <ul class="nav nav-pills">'+(orgsel_html.replace(/orgid_tpml/ig,'orgid_tpml_'+tpml_index))+'</ul>');
	tpml_index++;
}

function selJob(obj){
	var jobid=jQuery(obj).attr('_jobid');
	var li=jQuery(obj).parent().parent().parent();
	var html=obj.innerHTML;
	li.find('.dropdown-toggle').attr('_jobid',jobid).find('span').html(html);
	li.find('input').val(jobid);
}
function selDepart(obj){
	var orgid=jQuery(obj).val();
	var li=jQuery(obj).parent();
	li.parent().find('.job .dropdown-menu').load(ajaxurl+'do=getjobs&orgid='+orgid,function(html){
			if(checkAdminLogin(html)){
				location.reload();
			}
			if(li.parent().find('.job .dropdown-menu li').length>1) li.parent().find('.job .dropdown-toggle').trigger('click');
		});
	li.parent().find('.job .dropdown-toggle').attr('_jobid',0).find('span').html(__lang.none);
	li.parent().find('.job input').val('0');
}
function errormessage(id, msg,passlevel) {
	if(jQuery('#'+id).length > 0) {
		msg = !msg ? '' : msg;
		if(msg == 'succeed') {
			msg = '';
			jQuery('#suc_' + id).addClass('p_right');
		} else if(msg !== '') {
			jQuery('#suc_' + id).removeClass('p_right');
		}
		jQuery('#chk_' + id).find('kbd').html(msg);
		if(msg && !passlevel) jQuery('#'+id).parent().parent().addClass('has-warning');
		else jQuery('#'+id).parent().parent().removeClass('has-warning');
	}
}

function checkemail(id) {
	errormessage(id);
	var email = trim(jQuery('#'+id).val());
	email=email.toLowerCase();
	if(jQuery('#'+id).parent()[0].className.match(/ p_right/) && (email == '' || email == lastemail ) || email == lastemail) {
		return;
	} 
	if(email.match(/<|"/ig)) {
		errormessage(id, __lang.Email_sensitivity);
		return;
	}
	
	var x = new Ajax();
	jQuery('#suc_' + id).removeClass('p_right');
	jQuery.getJSON('user.php?mod=ajax&inajax=yes&infloat=register&handlekey=register&ajaxmenu=1&action=checkemail&email=' + email, function(json) {
		if(json.error){
			errormessage(id, json.error);
		}else{
			errormessage(id, 'succeed');
		}
		
	});
}
function checknick(id) {
	errormessage(id);
	var username = trim(jQuery('#'+id).val());
	if(jQuery('#chk_' + id).parent()[0].className.match(/ p_right/) && (username == '' || username == lastusername) || username == lastusername) {
		return;
	} 
	if(username.match(/<|"/ig)) {
		errormessage(id, __lang.profile_nickname_illegal);
		return;
	}
	if(username){
		var unlen = username.replace(/[^\x00-\xff]/g, "**").length;
		if(unlen < 3 || unlen > 30) {
			errormessage(id, unlen < 3 ? __lang.username_character : __lang.username_character);
			return;
		}
		var x = new Ajax();
		jQuery('#suc_' + id).removeClass('p_right');
		jQuery.getJSON('user.php?mod=ajax&inajax=yes&infloat=register&handlekey=register&ajaxmenu=1&action=checkusername&username=' + encodeURI(username), function(json) {
			if(json.error){
				errormessage(id, json.error);
			}else{
				errormessage(id, 'succeed');
			}
		});
	}
}
function checkPwdComplexity(firstObj, secondObj, modify) {
	modifypwd = modify || false;
	firstObj.onblur = function () {
		if(firstObj.value == '') {
			var pwmsg = !modifypwd ? __lang.register_password_tips :  __lang.js_change_password;
			if(pwlength > 0) {
				pwmsg += ', '+__lang.register_password_length_tips1+pwlength+__lang.register_password_length_tips2;
			}
			if(!modifypwd) errormessage(firstObj.id, pwmsg);
		}else{
			errormessage(firstObj.id, !modifypwd ? 'succeed' :  __lang.js_change_password);
		}
		checkpassword(firstObj.id, secondObj.id);
	};
	firstObj.onkeyup = function () {
		if(pwlength == 0 || jQuery('#'+firstObj.id).value.length >= pwlength) {
			var passlevels = new Array('',__lang.weak,__lang.center,__lang.strong);
			var passlevel = checkstrongpw(firstObj.id);
			
			errormessage(firstObj.id, '<span class="passlevel passlevel'+passlevel+'">'+__lang.intension+':'+passlevels[passlevel]+'</span>','passlevel');
		}
	};
	secondObj.onblur = function () {
		if(secondObj.value == '') {
			if(!modifypwd) errormessage(secondObj.id, !modifypwd ?'succeed' : __lang.register_repassword_tips);
		}
		checkpassword(firstObj.id, secondObj.id);
	};
}
function checkstrongpw(id) {
	var passlevel = 0;
	var el=document.getElementById(id);
	var val=el.value;
	if(val && val.match(/\d+/g)) {
		passlevel ++;
	}
	if(val && val.match(/[a-z]+/ig)) {
		passlevel ++;
	}
	if(val && val.match(/[^a-z0-9]+/ig)) {
		passlevel ++;
	}
	return passlevel;
}
function checkpassword(id1, id2) {
	if(!document.getElementById(id1).value && !document.getElementById(id2).value) {
		//return;
	}
	if(pwlength > 0) {
		if(document.getElementById(id1).value.length < pwlength) {
			errormessage(id1, __lang.password_too_short+pwlength+__lang.register_password_length_tips2);
			return;
		}
	}
	if(strongpw) {
		var strongpw_error = false, j = 0;
		var strongpw_str = new Array();
		for(var i in strongpw) {
			if(strongpw[i] === 1 && !document.getElementById(id1).value.match(/\d+/g)) {
				strongpw_error = true;
				strongpw_str[j] = __lang.strongpw_1;
				j++;
			}
			if(strongpw[i] === 2 && !document.getElementById(id1).value.match(/[a-z]+/g)) {
				strongpw_error = true;
				strongpw_str[j] = __lang.strongpw_2;
				j++;
			}
			if(strongpw[i] === 3 && !document.getElementById(id1).value.match(/[A-Z]+/g)) {
				strongpw_error = true;
				strongpw_str[j] = __lang.strongpw_3;
				j++;
			}
			if(strongpw[i] === 4 && !document.getElementById(id1).value.match(/[^A-Za-z0-9]+/g)) {
				strongpw_error = true;
				strongpw_str[j] = __lang.strongpw_4;
				j++;
			}
		}
		if(strongpw_error) {
			errormessage(id1, __lang.password_weak+strongpw_str.join('，'));
			return;
		}
	}
	errormessage(id2);
	if(document.getElementById(id1).value !=document.getElementById(id2).value) {
		errormessage(id2, __lang.profile_passwd_notmatch);
	} else {
		if(modifypwd) errormessage(id1,  'succeed' );
		errormessage(id2,  'succeed' );
		
	}
}
function jstree_search(val){
	console.log(val);
	if(val=='stop'){
		jQuery('#jstree_search_input').val('');
		jQuery('#searchval').val('');
		jQuery('.classtree-search').slideUp(500);
		jQuery("#classtree").jstree(true).search();
	}else{
		if(val==''){
			jQuery('#jstree_search_input').val('');
			jQuery('#searchval').val('');
			jQuery('.classtree-search').slideUp(500);
		}
		jQuery("#classtree").jstree(true).search(val);
	}
}
function jstree_create_organization(){
	var inst = jQuery("#classtree").jstree(true);
		jQuery.post(ajaxurl+'do=create',{'forgid':0,'t':new Date().getTime()},function(json){
			if(!json || json.error){
				showmessage(json.error,'danger',3000,1);
			}else if(json.orgid>0){
				var arr={"id":json.orgid,"text":json.orgname,"type":"organization","icon":'dzz/system/images/organization.png'}
				inst.create_node(inst.get_node('#'), arr, "first", function (new_node) {
					setTimeout(function () { inst.edit(new_node); },0);
				});
			}
		},'json');
}
function jstree_create_dir(){
	var inst = jQuery("#classtree").jstree(true),obj;
	if(inst.get_selected(true).length>0){
		obj=inst.get_selected(true);
		obj=obj[0];
	}else{
		showmessage(__lang.please_select_one_organization_department,'danger',1000,1);
		return;
	}
	if(obj.type=='user'){
		showmessage(__lang.please_select_one_organization_department,'danger',1000,1);
		 return true;
	}
	if(inst.is_disabled(obj)){
		return true;
	}
	var inst = jQuery("#classtree").jstree(true);
	jQuery.post(ajaxurl+'do=create',{'forgid':obj.id,'t':new Date().getTime()},function(json){
		if(!json || json.error){
			showmessage(json.error,'danger',3000,1);
		}else if(json.orgid>0){
			var arr={"id":json.orgid,"text":json.orgname,"type":"organization","icon":(json.forgid>0)?'dzz/system/images/department.png':'dzz/system/images/organization.png'}
			inst.create_node(obj, arr, "first", function (new_node) {
				setTimeout(function () { inst.edit(new_node); },0);
			});
		}
	},'json');
	
}
function jstree_create_user(flag){
	var inst = jQuery("#classtree").jstree(true),obj;
	if(inst.get_selected(true).length>0){
		obj=inst.get_selected(true);
		obj=obj[0];
	}else{
		if(flag) flag=0;
		obj=inst.get_node('#');
	}
	if(obj.type=='user'){
		obj=inst.get_node(obj.parent);
	}
	if(inst.is_disabled(obj)){
		return true;
	}
	showDetail(0,'user',null,obj.id);
}

function showDetail(id,idtype,ajaxdo,orgid){
	var hash=idtype+'_'+id;
	var urladd=''
	if(ajaxdo){
		hash+='_'+ajaxdo;
		urladd+='&do='+ajaxdo
	}
	if(orgid){
		hash+='_'+orgid;
		urladd+='&orgid='+orgid
	}
	currentHash=hash;
	location.hash=hash;
	//console..log(hash);
	urladd+='&t='+new Date().getTime()
	
	jQuery('#orguser_container').load(baseurl+'op=view&id='+id+'&idtype='+idtype+urladd,function(html){
		if(checkAdminLogin(html)){
			location.reload();
		}
	});
}

function open_node_dg(inst,node,arr){ //自动打开有权限的目录树
	 inst.open_node(node,function(node){
		 var i=jQuery.inArray(node.id,arr);
		 if(i<arr.length && i>-1 && document.getElementById(arr[i+1])) open_node_dg(inst,document.getElementById(arr[i+1]),arr);
		 else{
			// inst.select_node(node);
		 }
	 });
 }
 
function job_show_editor(jobid,orgid,obj){
	var el=jQuery(obj).addClass('hide');
	el.parent().find('.edit').removeClass('hide');
	el.parent().find('input').focus();
	jQuery(document).on('click.job_edit_'+jobid,function(event){
		if(!jQuery(event.target).closest(el.parent()).length){
			 job_save(jobid,orgid);
			 jQuery(document).off('click.job_edit_'+jobid);
		}
	});
}

function job_save(jobid,orgid){
	var el=jQuery('#job_'+jobid);
	var oname=trim(el.find('.job-name').html());
	var name=trim(el.find('.job-edit-control input').val());
	if(oname==name){
		el.find('.job-name').removeClass('hide');
		el.find('.edit').addClass('hide');
		return;
	}
	jQuery.post(ajaxurl+'do=jobedit',{'name':name,'jobid':jobid,'orgid':orgid,'t':new Date().getTime()},function(json){
		if(json.error){
			el.find('.job-name').html(oname).removeClass('hide');
			el.find('.edit').addClass('hide');
			el.find('.job-edit-control input').val(oname);
		}else if(json.jobid>0){
			el.find(' .job-name').html(json.name).removeClass('hide');
			el.find('.edit').addClass('hide');
			el.find('.job-edit-control input').val(json.name);
		}
	},'json');
}
function job_show_add_editor(orgid,obj){
	var el=jQuery(obj);
	el.addClass('hide');
	el.parent().find('.new-job-control').removeClass('hide');
	el.parent().find('.new-job-control input').focus();
	jQuery(document).on('click.new-job-'+orgid,function(event){
		if(!jQuery(event.target).closest(el.parent()).length){
			 job_cancel_add_editor(orgid);
			 jQuery(document).off('click.new-job-'+orgid);
		}
	});
}
function job_cancel_add_editor(orgid){
	var el=jQuery('.jobs .new-job');
	el.find('.new-job-control').addClass('hide');
	el.find('a').removeClass('hide');
	
}
function job_del(jobid,orgid){
	var el=jQuery('#job_'+jobid);
	jQuery.post(ajaxurl+'do=jobdel',{'jobid':jobid,'orgid':orgid,'t':new Date().getTime()},function(json){
		if(json.error){
			showmessage(json.error,'danger',3000,1);
		}else if(json.jobid>0){
			el.remove();
		}
	},'json');
}

function job_add(orgid){
	var newjob=jQuery('.jobs .new-job');
	var name=newjob.find('.new-job-text').val();
	if(name==''){
		newtodo.find('.new-job-text').focus();
		return;
	}
	jQuery.post(ajaxurl+'do=jobadd',{'name':name,'orgid':orgid,'t':new Date().getTime()},function(json){
		
		if(json.jobid>0){
			appendjob(json);
			newjob.find('.new-job-text').val('').focus();
		}else{
			showmessage(json.error,'danger',3000,1);
		}
	},'json');
}
function appendjob(json){
	var html='';
	html+='<div id="job_'+json.jobid+'" orgid="'+json.orgid+'" class="job-item-edit pull-left">';
    html+='    <button onclick="job_show_editor(\''+json.jobid+'\',\''+json.orgid+'\', this)" class="btn btn-simple job-name mr20">'+json.name+'</button>';
    html+='    <div class="edit hide" style="min-width:250px">';
    html+='        <div class="job-edit-control pull-left" >';
    html+='            <input type="text" class="form-control" style="width:100px" value="'+json.name+'" onkeyup="if(event.keyCode==13){job_save(\''+json.name+'\',\''+json.orgid+'\')}">';
    html+='        </div>';
    html+='        <button onclick="job_save(\''+json.name+'\',\''+json.orgid+'\')" data-loading-text="'+__lang.save+'" class="btn btn-success job-save">'+__lang.save+'</button>';
    html+='        <button class="btn btn-link todo-del" onclick="job_del(\''+json.name+'\',\''+json.orgid+'\')">'+__lang.delete+'</button>';
    html+='    </div> ';
    html+='</div>';
	jQuery('.jobs .new-job').before(html);
}
function callback_moderators(ids,data,orgid){
	console.log(ids);console.log(orgid);
	//删除不在选择列表内的用户
	jQuery('.moderators-container .user-item').each(function(){
		var uid=jQuery(this).attr('uid');
		if(jQuery.inArray(uid,ids)===-1){
			jQuery(this).find('.delete').trigger('click');
		}
	});
	for(var i=0;i<ids.length;i++){
		moderator_add(orgid,ids[i]);
	}
	
}
function moderator_add(orgid,uid){
	if(jQuery('#moderators_container_'+orgid+' .user-item[uid='+uid+']').length){
		jQuery('#moderators_container_'+orgid+' .user-item[uid='+uid+']').insertAfter(jQuery('#moderators_container_'+orgid+' .moderators-acceptor'));
		jQuery('#moderators_container_'+orgid+' .moderators-acceptor').removeClass('hover');
		return;
	}
	jQuery('#moderators_container_'+orgid+' .moderators-acceptor').removeClass('hover');
	jQuery.post(ajaxurl+'do=moderator_add',{'orgid':orgid,'uid':uid,'t':new Date().getTime()},function(json){
		if(json.error) showmessage(json.error,'danger',3000,1);
		else{
			appendModerator(json);
		}
	},'json');
}
function appendModerator(json){
	var html='';
	html+='<li class="user-item pull-left" uid="'+json.uid+'"> ';
	html+='			<a href="javascrip:;" class="delete" onclick="moderator_del(\''+json.id+'\',\''+json.orgid+'\',this);return false"><i style="color:#d2322d;font-size:16px" class="glyphicon glyphicon-remove-sign"></i></a>';
	html+='			<div class="avatar-cover"></div>';
	html+='			<div class="user-item-avatar">'; 
	html+='				<div class="avatar-face">';
	html+='					'+json.avatar; 
	html+='				</div>';
	html+='			</div>';
	html+='			<p class="text-center" style="height:20px;margin:5px 0;line-height:25px;overflow:hidden;"> '+json.username+'</p>';
	html+='	   </li>';
	jQuery('#moderators_container_'+json.orgid+' .moderators-acceptor').after(html);
	var inst = jQuery("#classtree").jstree(true);
	
	var node= inst.get_node('#'+json.orgid);
	inst.refresh_node(node);
}
function moderator_del(id,orgid,obj){
	jQuery.post(ajaxurl+'do=moderator_del',{'orgid':orgid,'id':id,'t':new Date().getTime()},function(json){
		if(json.error) showmessage(json.error,'danger',3000,1);
		else{
			jQuery(obj).parent().remove();
		}
		
	},'json');
}

function folder_available(available,orgid){
	jQuery.post(ajaxurl+'do=folder_available',{'orgid':orgid,'available':available,'t':new Date().getTime()},function(json){
		if(json.error){
			 showmessage(json.error,'danger',3000,1);
		}else{
			if(available){
				 showmessage(__lang.share_enable_successful,'success',3000,1);
				 //jQuery('#indesk').show();
			}else{
				showmessage(__lang.share_close_successful,'success',3000,1);
				//jQuery('#indesk').hide();
			}
		}
	},'json');
}
function group_on(on,orgid){
	jQuery.post(ajaxurl+'do=group_on',{'orgid':orgid,'available':on,'t':new Date().getTime()},function(json){
		if(json.error){
			showmessage(json.error,'danger',3000,1);
		}else{
			if(on){
				showmessage(__lang.group_on_successful,'success',3000,1);
			}else{
				showmessage(__lang.group_close_successful,'success',3000,1);
			}
		}
	},'json');
}
function folder_indesk(indesk,orgid){
	jQuery.post(ajaxurl+'do=folder_indesk',{'orgid':orgid,'indesk':indesk,'t':new Date().getTime()},function(json){
		if(json.error) showmessage(json.error,'danger',3000,1);
	},'json');
}
function set_org_logo(orgid,aid){
	jQuery.post(ajaxurl+'do=set_org_logo',{'orgid':orgid,'aid':aid},function(json){
		if(json.error) showmessage(json.error,'danger',3000,1);
	},'json');
}
/*function set_org_bgphoto(orgid,aid){
	jQuery.post(ajaxurl+'&do=set_org_bgphoto',{'orgid':orgid,'aid':aid},function(json){
		if(json.error) showmessage(json.error,'danger',3000,1);
	},'json');
}*/
function set_org_orgname(orgid,obj){
	var oldname=jQuery(obj).data('oldname');
	console.log(oldname);
	jQuery.post(ajaxurl+'do=set_org_orgname',{'orgid':orgid,'orgname':obj.value},function(json){
		if(json.error){
			obj.value=oldname;
			showmessage(json.error,'danger',3000,1);
		}else{
			 jQuery(obj).data('oldname',obj.value);
			 jQuery('#title_orgname').html(obj.value);
			 var node=jQuery("#classtree").jstree(true).get_node('#'+orgid);
			 jQuery("#classtree").jstree('refresh',node);
		}
	},'json');
}
function set_org_desc(orgid,desc){
	jQuery.post(ajaxurl+'do=set_org_desc',{'orgid':orgid,'desc':desc},function(json){
		if(json.error){
			showmessage(json.error,'danger',3000,1);
		}
	},'json');
}
function folder_maxspacesize(obj,orgid){
	jQuery.post(ajaxurl+'do=folder_maxspacesize',{'orgid':orgid,'maxspacesize':obj.value,'t':new Date().getTime()},function(json){
		if(json.error){
			 obj.value=json.val;
			 showmessage(json.error,'danger',3000,1);
		}else{
			jQuery('#'+orgid+' a.jstree-clicked').trigger('click');
			 showmessage('空间大小设置成功','success',3000,1);
		}
	},'json');
}
