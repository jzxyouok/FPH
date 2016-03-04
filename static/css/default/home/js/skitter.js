// JavaScript Document
(function(){
	$.fn.Skitter = function(opt){
		var defaults = {
			moveSpeed: 200,
			fn_click: null
		}
		var opts = $.extend({}, defaults, opt);
		var $this = $(this);
		
		var SK = {
			moveSpeed: opts.moveSpeed,
			fn: opts.fn_click,
			show_img: $this.find('.show_area img'),
			list_img: $this.find('.list_area img'),
			list_ul: $this.find('.list_area ul'),
			list_width: 0,
			arrow_left: $this.find('.list_area .arrow.left'),
			arrow_right: $this.find('.list_area .arrow.right'),
			ul_pos: 0,
			
			calcULwidth: function(){
				var marginRight = parseInt(SK.list_img.parents('li').css('margin-right'));
				var imgWidth = SK.list_img.width();
				var imgLength = SK.list_img.length;
				var moveDST = marginRight + imgWidth;
				var ulWidth = moveDST * imgLength;
				
				SK.list_width = ulWidth;
				SK.list_ul.width(ulWidth);
				
				SK.Hmove(moveDST);
				
			},
			calcMovePos: function(){
				SK.ul_pos = parseInt(SK.list_ul.css('margin-left'));
			},
			moveDst: function(){
				var parentWidth = $this.find('.list_area .blocks').width();
				var dst = SK.list_width - parentWidth;
				return dst;
			},
			
			Hmove: function(moveDST){
				SK.arrow_left.live('click',function(e){
					/*SK.calcMovePos();*/
					if(SK.ul_pos < 0){
						var pos = SK.ul_pos + moveDST;
						SK.ul_pos = pos;
						SK.list_ul.animate({'margin-left': pos+'px'},SK.moveSpeed);
					}
				});
				SK.arrow_right.live('click',function(e){
					/*SK.calcMovePos();*/
					if(SK.ul_pos > -SK.moveDst() + moveDST){
						var pos = SK.ul_pos - moveDST;
						SK.ul_pos = pos;
						SK.list_ul.animate({'margin-left': pos+'px'},SK.moveSpeed);
					}
					
				});
			},
			
			preview:function(fn){
				SK.list_img.live('click',function(){
					var $this = $(this);
					var index = SK.list_img.index($this);
					$this.parents('ul').find('a').removeClass('on').eq(index).addClass('on');
					SK.show_img.hide();
					SK.show_img.eq(index).fadeIn();
					
					if(fn!=null){
						fn(index);
					}
				});
			},
			
			setOn: function(){
				SK.list_img.eq(0).parents('a').addClass('on');
				SK.show_img.hide().eq(0).show();
			},
			
			init: function(){
				SK.calcULwidth();
				SK.moveDst();
				SK.preview(SK.fn);
				SK.setOn();
			}
		}
		
		
		return this.each(function(){
			SK.init();
		});
		
		
	}
})(jQuery);

