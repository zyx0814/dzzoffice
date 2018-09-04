var InputAnimate = {};
(function($){
	InputAnimate.init = function(obj){
		var self = this;	
		self.what = true;
		self.box = obj;
		if(obj.hasClass('input-animate')){
			self.what = false;
		}
		self.inputs = self.box.find('input.form-control,button.form-control,textarea.form-control,select.form-control,.tagsinput input');
		if(!self.inputs.length)return false;
		if(self.what){
			self.label = self.box.find('.input-label');
			var length = obj.find('.input-animate').length;
			if(self.box.hasClass('disabled')){//需要disabled时
				self.box.find('input.form-control,button.form-control,textarea.form-control,select.form-control,.tagsinput input').attr("disabled",true);
			}
			obj.find('.input-animate').each(function(i){
				var inputs = $(this).find('input.form-control,button.form-control,textarea.form-control,select.form-control,.tagsinput input');
				if(self.box.hasClass('input-float')){//是否为float样式
					self.float_style(inputs);
					self.float_change(inputs);
				}else if(self.box.hasClass('input-fixation') && (i == 0)){//是否为Fixation样式
					self.fixation_style($(this));
				}
				self.input_style($(this),inputs,i,length);
				self.input_event($(this),inputs);//绑定事件
			})
		}else{
			self.input_style(self.box,self.inputs,1,2);
			self.input_event(self.box,self.inputs);//绑定事件
		}
		
	}
	InputAnimate.float_style = function(obj){
		var self = this;
		if(obj.length && ($.trim(obj.val()) || obj.get(0).tagName == 'SELECT'))self.box.addClass('focus');
	}
	InputAnimate.fixation_style = function(obj){
		var self = this;
		obj.css('margin-left',self.label.outerWidth());
	}
	InputAnimate.input_style = function(animate,obj,i,length){
		var self = this;
		if(obj.prop('nodeName') == 'TEXTAREA')obj.TextAreaExpander(30, 99999);
		if(self.box.find('.input-icon').length && (i == length-1)){
			var w = self.box.find('.input-icon').outerWidth()+10;
			animate.css('padding-right',w);
		}
	}
	InputAnimate.create_animate = function(){
		$('.input-black.active').removeClass('active');
	}
	InputAnimate.input_event = function(obj,inputs){
		var self = this;
		obj.on('click',function(){
			if(!$(this).find('input.form-control,textarea.form-control').prop("disabled")){
				self.create_animate();				
				$(this).closest('.input-black').addClass('active');
				$(this).addClass('animate');
				$(this).find('input.form-control,textarea.form-control').focus();
			}
			
		});
		inputs.on('focus',function(){	
			$(this).trigger('input');
			$(this).closest('.input-black').addClass('active');
			$(this).closest('.input-animate').addClass('animate');
		});
		inputs.on('blur',function(){
			if($(this).closest('.input-black').hasClass('input-float')){//是否为float样式			
				if(this.tagName != 'SELECT'){
					if($(this).closest('.tagsinput').length && $(this).closest('.input-animate').find('input.form-control').val()){//兼容tagsinput
						$(this).closest('.input-black').addClass('focus');
					}else if($.trim($(this).val())){
						$(this).closest('.input-black').addClass('focus');
					}else{
						$(this).closest('.input-black').removeClass('focus');
					}
				}else{
					$(this).closest('.input-black').addClass('focus');
				}
			}
			$(this).closest('.input-black').removeClass('active');
			$(this).closest('.input-animate').removeClass('animate');
			
		});
		if(self.label){
			self.label.on('click',function(){
				$(this).siblings('.input-animate').click();
			});
		}
	};
	InputAnimate.float_change = function(obj){
		obj.change(function(){
			if($(this).closest('.tagsinput').length && $(this).closest('.input-animate').find('input.form-control').val()){//兼容tagsinput
				$(this).closest('.input-black').addClass('focus');
			}else if($.trim($(this).val())){
				$(this).closest('.input-black').addClass('focus');
			}else{
				$(this).closest('.input-black').removeClass('focus');
			}
		});	
	};
	$(document).ready(function(){
		$('.input-black').each(function() {			
			InputAnimate.init($(this));	
		});
	});
})(jQuery);
	