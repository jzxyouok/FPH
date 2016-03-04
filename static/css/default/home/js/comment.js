/*
date: 20141028
作用： 公共js
*/
$(function(){
	
	//楼盘搜索
	$('.J_property_but').click(function(){
		var search_val = $('#search').val();
		if(search_val=='请输入楼盘名称关键词...'){
			$('#search').focus();
			return false;
		}
		$('#from').submit();
		return false;
	});
	
	//选择户型
	$('.J_select_huxing_img > ul > li').click(function(){
		var id = $(this).attr('rel');
		if(id==''){
			alert('参数出差');
			return false;
		}
		$('.J_img_none img').hide();
		$('img[rel="'+id+'"]').show();
		var url = PINER.root + '/?g=home&m=property&a=ajax_apartment';
		$.post(url,{id:id},function(result){
			if(result.status == 1){
				if(result.data.house_room==''){result.data.house_room=0;}
				if(result.data.house_hall==''){result.data.house_hall=0;}
				if(result.data.house_wc==''){result.data.house_wc=0;}
				$('.J_info_data').html('<h2>'+result.data.house_name+'<i class="status_tip">'+result.data.hxtitle+'</i></h2><span>户型：'+result.data.house_room+'室'+result.data.house_hall+'厅'+result.data.house_wc+'卫</span><div class="BC brokerage"><span>卖点</span></div>'+result.data.house_info+'');
				return false;
            } else {
				alert(result.msg);
				return false;
            }
		},'json');
	});
	
	//上一户型
	$('.J_property_left').live('click',function(){
		var id = $('.on').parent('li').prev('li').attr('rel');
		if(id){
			$('.J_select_huxing_img > ul > li > a').removeClass('on');
			$('li[rel="'+id+'"]').find('a').addClass('on');
			var img_url = $('li[rel="'+id+'"]').find('img').attr('src');
			$('.J_img_none img').hide();
			$('img[rel="'+id+'"]').show();
			var url = PINER.root + '/?g=home&m=property&a=ajax_apartment';
			$.post(url,{id:id},function(result){
				if(result.status == 1){
				if(result.data.house_room==''){result.data.house_room=0;}
				if(result.data.house_hall==''){result.data.house_hall=0;}
				if(result.data.house_wc==''){result.data.house_wc=0;}
				$('.J_info_data').html('<h2>'+result.data.house_name+'<i class="status_tip">'+result.data.hxtitle+'</i></h2><span>户型：'+result.data.house_room+'室'+result.data.house_hall+'厅'+result.data.house_wc+'卫</span><div class="BC brokerage"><span>卖点</span></div>'+result.data.house_info+'');
					return false;
				} else {
					alert(result.msg);
					return false;
				}
			},'json');
		}
	});
	
	//下一户型
	$('.J_property_right').live('click',function(){
		var id = $('.on').parent('li').next('li').attr('rel');
		if(id){
			$('.J_select_huxing_img > ul > li > a').removeClass('on');
			$('li[rel="'+id+'"]').find('a').addClass('on');
			var img_url = $('li[rel="'+id+'"]').find('img').attr('src');
			$('.J_img_none img').hide();
			$('img[rel="'+id+'"]').show();
			var url = PINER.root + '/?g=home&m=property&a=ajax_apartment';
			$.post(url,{id:id},function(result){
				if(result.status == 1){
				if(result.data.house_room==''){result.data.house_room=0;}
				if(result.data.house_hall==''){result.data.house_hall=0;}
				if(result.data.house_wc==''){result.data.house_wc=0;}
				$('.J_info_data').html('<h2>'+result.data.house_name+'<i class="status_tip">'+result.data.hxtitle+'</i></h2><span>户型：'+result.data.house_room+'室'+result.data.house_hall+'厅'+result.data.house_wc+'卫</span><div class="BC brokerage"><span>卖点</span></div>'+result.data.house_info+'');
				return false;
				} else {
					alert(result.msg);
					return false;
				}
			},'json');
		}
	});
	
	
	//楼盘品牌
	$('.J_brand_sub').click(function(){
		var search_name = $('#search_name').val();
		if(search_name=='请输入品牌名称关键词...'){
			$('#search_name').focus();
			return false;
		}
		$('#form').submit();
		return false;
	});
		   
		   
})

//点击input里的提示文字消失
//<input onFocus="focusInputEle(this)" onBlur="blurInputEle(this)" defaultVal="请输入搜索关键字" value="请输入搜索关键字" />	
function getAttributeValue(o, key) {
	if (!o.attributes) return null;
		var attr = o.attributes;
		for (var i = 0; i < attr.length; i++){
			if (key.toLowerCase() == attr[i].name.toLowerCase())
			return attr[i].value;
		}
		return null;
	}
	function focusInputEle(o) {
		if (o.value == getAttributeValue(o, 'defaultVal')){
			o.value = '';
			o.style.color = "#000000";//输入文字的颜色
		}
	}
	function blurInputEle(o) {
		if (o.value == '') {
		o.value = getAttributeValue(o, 'defaultVal');
		o.style.color = "#999999";//提示文字的颜色
	}
}