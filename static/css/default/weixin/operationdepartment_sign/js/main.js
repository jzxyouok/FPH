$(function(){

	//日历组件
	$("#datePicker").datepicker({
		changeMonthType:false,
		changeYear:false,
		maxDate:0, //只能选择今天
		minDate:0,
		dateFormat:"yymmdd",
		dayNamesMin:["日","一","二","三","四","五","六"],
		monthNames:["一月","二月","三月","四月","五月","六月","七月","八月","九月","十月","十一月","十二月"]
	});

	window.localStorage.clear();
	// window.localStorage.setItem("20150702","20150702");
	//检查今天是否已签到
	var currentDate= $( "#datePicker" ).val();	
	if( window.localStorage.getItem(currentDate) ){

		//已签到
		$("#checkBtn").addClass('disabled').text('已签到');
		$(".ui-state-active").addClass('checked');

	}

	//检查往日是否已签到
	var signdate = $('#contain').attr('rel');
	var singcount= $('#count').attr('rel');
	var lastsign= $('#lastsign').attr('rel');
	var jsbase   = new Base64();
		signdate = jsbase.decode(signdate);	
	var arrdate  = new Array();
	    arrdate  = signdate.split(',')
	var len = $(".ui-state-default").length;
	var num = parseInt( currentDate.substring(6) );
	var ti  = currentDate.substr(0,6);
	for(var i=1;i<=num;i++){
		var j=i;
		if(j<10){
			j='0'+j;
		}
		var key=ti+j; //查询本地存储
		if(signdate.length > 0)
		{
			for(var k=0 ;k < singcount; k++)
			{

				if ($(".ui-state-default").eq(i-1).text() == arrdate[k]) {
					$(".ui-state-default").eq(i-1).addClass('checked');
				};
				if( lastsign == currentDate) $("#checkBtn").addClass('disabled').text('已签到');

			}
		}		
	}
	

	$("#checkBtn").one('click',function(){
		var nickname = $('#nickname').val();
		var attr = $(this).hasClass('disabled');		
		if(attr) return false;
		$.post(URL,{openid:OPENID,nickname:nickname},function(data){
			if(data.status == 1){			
				$("#checkBtn").addClass('disabled').text('已签到');
				$(".ui-state-active").addClass('checked');
			}			
		},'json');
		//本地存储签到数据
		window.localStorage.setItem(currentDate,currentDate);

	});
});

/**
*
*  Base64 encode / decode
*
*  @author haitao.tu
*  @date   2010-04-26
*  @email  tuhaitao@foxmail.com
*
*/
 
function Base64() {
 
	// private property
	_keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
 
	// public method for encoding
	this.encode = function (input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;
		input = _utf8_encode(input);
		while (i < input.length) {
			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);
			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;
			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}
			output = output +
			_keyStr.charAt(enc1) + _keyStr.charAt(enc2) +
			_keyStr.charAt(enc3) + _keyStr.charAt(enc4);
		}
		return output;
	}
 
	// public method for decoding
	this.decode = function (input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;
		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
		while (i < input.length) {
			enc1 = _keyStr.indexOf(input.charAt(i++));
			enc2 = _keyStr.indexOf(input.charAt(i++));
			enc3 = _keyStr.indexOf(input.charAt(i++));
			enc4 = _keyStr.indexOf(input.charAt(i++));
			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;
			output = output + String.fromCharCode(chr1);
			if (enc3 != 64) {
				output = output + String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				output = output + String.fromCharCode(chr3);
			}
		}
		output = _utf8_decode(output);
		return output;
	}
 
	// private method for UTF-8 encoding
	_utf8_encode = function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";
		for (var n = 0; n < string.length; n++) {
			var c = string.charCodeAt(n);
			if (c < 128) {
				utftext += String.fromCharCode(c);
			} else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			} else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
 
		}
		return utftext;
	}
 
	// private method for UTF-8 decoding
	_utf8_decode = function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;
		while ( i < utftext.length ) {
			c = utftext.charCodeAt(i);
			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			} else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			} else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}
		}
		return string;
	}
}
