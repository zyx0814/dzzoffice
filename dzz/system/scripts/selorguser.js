var selorg={};

//添加
selorg.add=function(ctrlid,vals){
	//jQuery('#'+ctrlid).empty();
	for(var i in vals){
		if(jQuery('#'+ctrlid+'_sl_'+vals[i].orgid).length){
			continue;
		}
		var html='';
		if(vals[i].orgid.indexOf('uid_')===0){
			var headimg = '';
			if(vals[i].icon){
				headimg = '<img src="'+vals[i].icon+'" class="img-circle" title="'+vals[i].text.replace(/<em.+?<\/em>/i,'')+'">';
			}
		    html='<li id="'+ctrlid+'_sl_'+vals[i].orgid+'" class="right-classa-depart" data-val="'+vals[i].orgid+'">' +
				headimg+vals[i].text+' <i class="ti-close dzz dzz-close"></i></li>';
		}else{
			html='<li id="'+ctrlid+'_sl_'+vals[i].orgid+'" class="right-classa-depart" data-val="'+vals[i].orgid+'">'+vals[i].text+' <i class="ti-close dzz dzz-close"></i></li>';
		}
		jQuery('#'+ctrlid).append(html);
		
	}
	selorg.set(ctrlid);
	try{
		update_selected_sum();
	}catch(e){}
};

//删除
selorg.del=function(ctrlid,vals){
	for(var i in vals){
		if(jQuery('#'+ctrlid+'_sl_'+vals[i]).find('i.ti-close').length > 0){
			jQuery('#'+ctrlid+'_sl_'+vals[i]).remove();
		}
	}
	 selorg.set(ctrlid);
	try{
		update_selected_sum();
	}catch(e){}
};

//设置输入框的值
selorg.set=function(ctrlid){
	var val=[];
	jQuery('#'+ctrlid+' li').each(function() {
        val.push(jQuery(this).data('val'));
   });
	jQuery('#sel_'+ctrlid).val(val.join(','));
};
//y移除，并且取消机构树中的选择
 selorg.remove=function(ctrlid,obj){
	var unsel_val=jQuery(obj).parent().data('val');
	jQuery(obj).parent().remove();
	selorg.set(ctrlid);
	try{window.frames[ctrlid+'_iframe'].selectorg_remove(unsel_val);}catch(e){}
	try{
		update_selected_sum();
	}catch(e){}
};
 selorg.search=function(ctrlid,obj){
	 try{window.frames[ctrlid+'_iframe'].selectorg_search(obj.value);}catch(e){}
 };