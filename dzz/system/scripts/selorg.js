

var selorg={};

//添加
selorg.add=function(ctrlid,vals){
	for(var i in vals){
		if(jQuery('#'+ctrlid+'_sl_'+vals[i].orgid).length) continue;
		var html='';
		html+='<button id="'+ctrlid+'_sl_'+vals[i].orgid+'" type="button" class="btn btn-simple btn-sorg" data-val="'+vals[i].orgid+'">';
		html+='<img src="'+vals[i].icon+'">&nbsp;';
		html+=vals[i].path;
		html+='<a href="javascript:;" class="ibtn glyphicon glyphicon-remove" style="width:20px !important;height:20px;line-height:20px;margin-top:-3px;margin-right:-10px;" title="'+__lang.delete+'" onclick="selorg.remove(\''+ctrlid+'\',this);"></a>';
		jQuery('#'+ctrlid).append(html);
	}
	selorg.set(ctrlid);
}

//删除
selorg.del=function(ctrlid,vals){
	for(var i in vals){
		jQuery('#'+ctrlid+'_sl_'+vals[i]).remove();
	}
	 selorg.set(ctrlid);
}

//设置输入框的值
selorg.set=function(ctrlid){
	var val=[];
	jQuery('#'+ctrlid+' button').each(function() {
        val.push(jQuery(this).data('val'));
    });
	jQuery('#sel_'+ctrlid).val(val.join(','));
}
//y移除，并且取消机构树中的选择
 selorg.remove=function(ctrlid,obj){
	var unsel_val=jQuery(obj).parent().data('val');
	jQuery(obj).parent().remove();
	selorg.set(ctrlid)
	try{window.frames[ctrlid+'_iframe'].selectorg_remove(unsel_val);}catch(e){}
}