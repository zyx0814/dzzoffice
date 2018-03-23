/*
 * input: 隐藏的输入框
 * colors: 颜色块数组
 *
 */
jQuery.fn.colorPicker = function (conf) {
	// Config for plug
	var config = jQuery.extend({
		id:			'jquery-color-picker',	// id of colour-picker container
		speed:		500,					// Speed of dialogue-animation
		colors:['FFFFFF','E26F50','EE8C0C','FDE163','91CE31','3497DB','82939E','B2C0D1',''],
		input: '',
		changeBG:true
	}, conf);

	// Inverts a hex-colour
	var hexInvert = function (hex) {
		var r = hex.substr(0, 2);
		var g = hex.substr(2, 2);
		var b = hex.substr(4, 2);

		return 0.212671 * r + 0.715160 * g + 0.072169 * b < 0.5 ? 'ffffff' : '000000'
	};

	// Add the colour-picker dialogue if not added
	var colorPicker = jQuery('#' + config.id);

	if (!colorPicker.length) {
		colorPicker = jQuery('<div id="' + config.id + '"></div>').appendTo(document.body).hide();

		// Remove the colour-picker if you click outside it (on body)
		jQuery(document.body).click(function(event) {
			if (!(jQuery(event.target).is('#' + config.id) || jQuery(event.target).parents('#' + config.id).length)) {
				colorPicker.fadeOut(config.speed);
			}
		});
	}

	// For every select passed to the plug-in
	return this.each(function () {
		// Insert icon and input
		/*var select	= jQuery(this);
		var icon	= jQuery('<a href="#"><img src="' + config.ico + '" alt="' + config.openTxt + '" /></a>').insertAfter(select);*/
		var icon=jQuery(this);
		var input	= config.input.length?config.input:jQuery('<input type="hidden" name="' + icon.attr('name') + '" value="' + icon.attr('val') + '" size="6" />').insertAfter(icon);
		var loc		= '';
		
		// Build a list of colours based on the colours in the select
		
		for(var i in config.colors){
			loc += '<li><a href="#" title="#' 
					+ (config.colors[i]==''?'重置颜色': config.colors[i])
					+ '" rel="' 
					+ config.colors[i] 
					+ '" style="background: #' 
					+ config.colors[i] 
					+ '; colour: ' 
					+ hexInvert(config.colors[i]) 
					+ ';">' 
					+(config.colors[i]==''?'重置颜色': '#'+config.colors[i] )
					+ '</a></li>';
		}
		// Remove select
		//select.remove();

		// If user wants to, change the input's BG to reflect the newly selected colour
		
			input.change(function () {
				if(config.changeBG) {
					icon.css({background: input.val(), color: '#' + hexInvert(input.val())});
				}
			});

			input.change();
		
		
		// When you click the icon
		icon.click(function () {
			// Show the colour-picker next to the icon and fill it with the colours in the select that used to be there
			var iconPos	= icon.offset();
			var heading	= config.title ? '<h2>' + config.title + '</h2>' : '';
			var clientWidth=document.documentElement.clientWidth;
			var left=iconPos.left;
			if(iconPos.left>clientWidth/2){
				left=iconPos.left+icon.outerWidth(true)-colorPicker.outerWidth(true);
			}
			colorPicker.html(heading + '<ul>' + loc + '</ul>').css({
				position: 'absolute', 
				left: left + 'px', 
				top: iconPos.top + 'px'
			}).fadeIn(config.speed);

			// When you click a colour in the colour-picker
			jQuery('a', colorPicker).click(function () {
				// The hex is stored in the link's rel-attribute
				var hex = jQuery(this).attr('rel');
				 
				
				// If user wants to, change the input's BG to reflect the newly selected colour
				if(hex==''){
					input.val('');
					if(config.changBG) icon.css({background: ''});
					input.attr('title','');
				}else{
					input.val('#'+hex);
					input.attr('title','#'+hex);
					if(config.changBG) icon.css({background: '#' + hex});
				}
					
				// Trigger change-event on input
				input.change();

				// Hide the colour-picker and return false
				colorPicker.fadeOut(config.speed);

				return false;
			});

			return false;
		});
	});
};
