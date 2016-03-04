// JavaScript Document
/********************************************************************************************************
 * jQuery Calendar
 * @name Calendar.js
 * @description Dropdown Calendar
 * @author Qianming Fang 方钱明
 * @version 2.5.2
 * @compatibility IE7-10, safari, firefox, chrome, opera
 * @date 1/8/2011
 * @update 03/20/2014
 * @hotmail ukinhabitant@hotmail.com
 * @QQ 259393164
 * @statement 此段信息不会影响代码执行速度，请尊重作者劳动成果。如果要使用此插件，请勿擦拭此段说明。
 ********************************************************************************************************/

(function($){
	$.fn.Calendar = function(opt){
		var defaults = {
			showtime: false,
			fn_click: null
		};
		
		var opts = $.extend({}, defaults, opt);
		var $this = $(this);
		
		return this.each(function(){
			CLD.Create($this,opts.fn_click,opts.showtime);
		});
		
	};
	
	var CLD = {
		cmi: 0,
		cyi: 0,
		mouseon: false,
		divisor: 5,
		
		Create: function(txt,fn_click,showtime){
			var randomid = CLD.RandomID();
			var cld = '<div class="calender_frm" id='+ randomid +'>' +
						'<div class="arrow prev"><a href="javascript:void(0);"></a></div>' +
  						'<div class="arrow next"><a href="javascript:void(0);"></a></div>' + 
						'<div class="top">Loading...</div>' + 
						'<div class="date_frm">' +
							'<div class="date_name">' +
							  '<ul>' +
								'<li>日</li>' +
								'<li>一</li>' +
								'<li>二</li>' +
								'<li>三</li>' +
								'<li>四</li>' +
								'<li>五</li>' +
								'<li>六</li>' +
							  '</ul>' +
							'</div>' +
							'<div class="datetime">' +
							  '<ul>' +
								CLD.List() +
							  '</ul>' +
							'</div>' +
							'<div class="time">时间：' +
							  '<select name="hour" class="hour">' +
								CLD.NumList(0,23) +
							  '</select>' +
							  '<label>:</label>' +
							  '<select name="minute" class="minute">' +
								CLD.NumList(0,59,CLD.divisor) +
							  '</select>' +
							  '<a href="javascript:void(0)">确定</a>' +
							'</div>' +
						'</div>' +
					  '</div>';
			
			$('body').prepend(cld);
			
			var object = $('#' + randomid);
			/*CLD.SetPosition(txt, object);*/
			CLD.SetAbsoluteCenter(object);
			CLD.init(txt,object,fn_click,showtime);
			CLD.MouseOver(txt,object);
		},
		List: function(){
			var list = '';
			for(var i=0;i<42;i++){
				list += '<li><a href="javascript:void(0)"></a></li>';
			}
			return list;
		},
		NumList: function(begin,end,divisor){
			var options;
			var reg = new RegExp('^\\d+$');
			if(divisor==null){
				divisor = 1;
			}
			for(var i=begin;i<=end;i++){
				if((i/divisor).toString().match(reg)) {
					i = CLD.FormatTime(i);
					options += '<option value="'+i+'">'+i+'</option>';
				}
			}
			return options;
		},
		
		/*设置位置*/
		SetPosition: function(txt,object){
			var cld_left = txt.offset().left;
			var cld_top = txt.offset().top;
			var txt_height = txt.height()+6;
			object.css({
				'left': cld_left + 'px',
				'top': cld_top + txt_height + 'px'
			});
		},
		/*设置正中位置*/
		SetAbsoluteCenter: function(object){
			object.addClass('absoluteCenter');
		},
		
		dt: function(date){
			var dt = null;
			date==null ? dt = new Date() : dt = new Date(date);
			return dt;
		},
		year: function(){
			return CLD.dt().getFullYear();
		},
		month: function(){
			return CLD.dt().getMonth()+1;
		},
		date: function(){
			return CLD.dt().getDate();
		},
		day: function(){
			return CLD.dt().getDay();
		},
		firstday: function(m,y){
			return CLD.dt(y+'/'+m+'/01').getDay();
		},
		lastday: function(){
			return CLD.dt(CLD.year()+'/'+(CLD.month()+1)+'/01').getDay()-1;
		},
		days: function(m,y){
			return new Date(y,m,0).getDate();
		},
		
		SetTopTitle: function(object,m,y){
			object.find('.top').text(y+'年'+m+'月');
		},
		
		SetDays: function(object,m,y){
			var firstday = CLD.firstday(m,y);
			var lis = object.find('.datetime li');
			lis.find('a').empty();

			for(var i=0;i<CLD.days(m,y);i++){
				with(lis.eq(firstday).find('a')){
					removeClass('on');
					if(m==CLD.month()){
						if(CLD.date()==i+1){addClass('on');}
					}
					text(i+1);
				}
				firstday++;
			}
			
			CLD.cmi = m;
			CLD.cyi = y;
		},
		
		MouseOver: function(txt,object){
			object.hover(function(){
				CLD.mouseon = true;
			}, function(){
				CLD.mouseon = false;
			});
			txt.hover(function(){
				CLD.mouseon = true;
			}, function(){
				CLD.mouseon = false;
			});
		},
		
		HideOther: function(object) {
			$('.calender_frm').not(object).hide();
		},
	
		BindClick: function(txt,object,m,y,fn_click,showtime){
			object.delegate('.prev','click',function(){
				var cmi = CLD.cmi;
				var cyi = CLD.cyi;
				if(cmi > 1){
					cmi--;
					CLD.Config(object,cmi,cyi);
				}else{
					cyi--; cmi = 12;
					CLD.Config(object,cmi,cyi);
				}
			});
			
			object.delegate('.next','click',function(){
				var cmi = CLD.cmi;
				var cyi = CLD.cyi;
				if(cmi < 12){
					cmi++;
					CLD.Config(object,cmi,cyi);
				}else{
					cyi++; cmi = 1;
					CLD.Config(object,cmi,cyi);
				}
			});
			
			object.delegate('.datetime a','click',function(){
				var $this = $(this);
				if($this.text()!=''){
					var sym = '-';
					var date_format = CLD.cyi + sym + CLD.cmi + sym + $this.text();
					if(showtime){
						date_format += ' ' + CLD.TimeString(object);
					}
					txt.val(date_format);
					object.hide();
					if(fn_click!=null){
						fn_click();
					}
				}
			});
			
			object.delegate('.time a','click',function(){
				var date = txt.val().substr(0,9).replace(/\s+/g,'');
				txt.val(date + ' ' + CLD.TimeString(object));
				object.hide();
				if(fn_click!=null){
					fn_click();
				}
			});
			
			txt
			.focus(function(){
				/*CLD.SetPosition(txt,object);*/
				CLD.SetAbsoluteCenter(object);
				CLD.HideOther(object);
				CLD.Config(object,CLD.cmi,CLD.cyi);
				CLD.mouseon = true;
				object.fadeIn(200);
			})
			.blur(function(){
				if(!CLD.mouseon) {object.hide();}
			})
			;
		},
		
		Config: function(object,m,y){
			CLD.SetTopTitle(object,m,y);
			CLD.SetDays(object,m,y);
		},
		
		init: function(txt,object,fn_click,showtime,m,y){
			if(m==null){m = CLD.month();}
			if(y==null){y = CLD.year();}
			if(showtime){
				CLD.SetCSS(object);
			}
			CLD.Config(object,m,y);
			CLD.BindClick(txt,object,m,y,fn_click,showtime);
		},
		SetCSS: function(object){
			object.height(187).find('.time').show();
		},

		/*随机生成ID字符串*/
		RandomID: function () {
			/*alert(parseInt(Math.random()*99+1)); //1-99
			alert(parseInt(Math.random()*(999-100+1)+100)); //100-999*/
			var dt = new Date();
			var d = dt.getDate().toString();
			var h = dt.getHours().toString();
			var m = dt.getMinutes().toString();
			var s = dt.getSeconds().toString();
			var ms = dt.getMilliseconds().toString();
			var rn = parseInt(Math.random() * 99 + 1);
			var IDstr = 'JQUERY_CLD_';
			IDstr += d + h + m + s + ms + rn;
			return IDstr;
		},
		
		TimeString: function(object){
			var sym = ':';
			var dt = new Date();
			var h = dt.getHours().toString();
			var m = dt.getMinutes().toString();
			var sel_hour = object.find('.hour');
			var sel_minute = object.find('.minute');
			return sel_hour.val() + sym + sel_minute.val();
		},
		
		FormatTime: function(obj){
			if(obj.toString().length<2){
				obj = '0' + obj;
			}
			return obj;
		}
		
	};
	
})(jQuery);