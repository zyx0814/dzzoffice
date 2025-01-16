/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
(function($)
{
	//左右分栏时，调用此，可以实现点击隐藏左侧分栏，拖动改变左侧分栏的宽度
	$.fn.dzzvote = function(options)
	{
		var opt={	ajaxurl:DZZSCRIPT+'?mod=dzzvote&op=ajax',
					//uploadurl:null,
					voteid:'',
				}
	  	options=$.extend(opt,options);
		var container=$(this);
		var $this=$(this);
		var tmpl='<div class="row mb-3 dzzvote-post-text-item">';
           tmpl+='  <label class="col-sm-2 col-form-label"></label>';
           tmpl+='   <div class="col-sm-10">';
           tmpl+='       <div class="input-group mb-3">';
		   tmpl+='          <span class="input-group-text"><span class="badge bg-primary"></span></span>';
           tmpl+='          <input type="text" class="form-control" name="voteitemnew[content][]" value="">';
		   tmpl+='          <input type="hidden" name="voteitemnew[aid][]" value="">';
		   tmpl+='          <span class="input-group-text"><a href="javascript:;" data-itemid="0" class="dzzvote-post-delitem mdi mdi-delete lead dcolor"></a></span>';
           tmpl+='       </div>';
           tmpl+='   </div>';
           tmpl+=' </div>';
		var item_refresh=function(){
			container.find('#dzzvote_post_text_'+options['voteid']+' .dzzvote-post-item-container .badge').each(function(index){
				this.innerHTML=index+1;
			});
			dzzvote_maxselect_num();
		}
		var dzzvote_maxselect_num=function(){
			var val=parseInt(jQuery('#dzzvote_maxselect_num_'+options['voteid']+'').val());
			if(jQuery('#dzzvotetype_'+options['voteid']).val()>1){
				var sum=container.find('.dzzvote-post-image-item').length;
			}else{
				var sum=container.find('.dzzvote-post-text-item').length;
			}
			var option='';
			for(var i=1 ;i<=sum; i++){
				if(i==val){
					option+='<option value="'+i+'" checked="checked">'+__lang.most_can_choose+i+__lang.item+'</option>';
				}else{
					option+='<option value="'+i+'">'+__lang.most_can_choose+i+__lang.item+'</option>';
				}
			}
			jQuery('#dzzvote_maxselect_num_'+options['voteid']+'').html(option);
		}
		var item_add=function(){
			
			jQuery(tmpl).appendTo('#dzzvote_post_text_'+options['voteid']+' .dzzvote-post-item-container');
			item_refresh();
		}
		var item_delete=function(){
			if($(this).data('itemid')>0){
				var self=this;
				$.getJSON(options.ajaxurl+'&do=itemdelete&itemid='+$(this).data('itemid'),function(json){
					jQuery(self).closest('.dzzvote-post-image-item,.dzzvote-post-text-item').remove();
					item_refresh();
				});
			
			}else{
				jQuery(this).closest('.dzzvote-post-image-item,.dzzvote-post-text-item').remove();
				item_refresh();
			}
		}
		var init=function(){
			container.on('click.dzzvote','.dzzvote-post-additem',item_add);
			container.on('click.dzzvote','.dzzvote-post-delitem',item_delete);
		}
		init();
	}
	
})(jQuery);
