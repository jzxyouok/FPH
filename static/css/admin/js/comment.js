//后台js
$(function(){
	//设为封面
	$('.J_feng').live('click',function(){
		var one = $('.photo img').eq(0).attr('src');
		var oneimg = $('.photo').eq(0).children('.picarr').val();
		var onerel = $('.J_img_del').eq(0).attr('rel');
		var oneup_input = $('.image_up').find('.up_input').eq(0).val();
		
		var src = $(this).parent().prev().attr('src');
		var srcimg = $(this).parent().prevAll('.picarr').val();
		var srcrel = $(this).next().attr('rel');  
		var up_input = $(this).parents('ul').find('.up_input').val();
		
		//替换封面
		$('.photo img').eq(0).attr('src',src);
		$('.photo a').eq(1).attr('rel',srcrel);
		$('.photo').eq(0).children('.picarr').val(srcimg)
		$('.image_up').find('.up_input').eq(0).val(up_input);
		//替换当前
		$(this).next().attr('rel',onerel);
		$(this).parent().prev().attr('src',one);
		$(this).parent().prevAll('.picarr').val(oneimg)
		$(this).parents('ul').find('.up_input').val(oneup_input);
	});
	
	//删除图片
	$('.J_img_del').live('click',function(){
		var th = $(this);
		var name = th.parents('li').children('.picarr').val();
		var url      = PINER.root + '/?g=admin&m=property&a=del_imgarr&roleid=1';
		if(name==''){
			$.pinphp.tip({content:'参数出错，删除失败！', icon:'error'});
			return false;
		}
		$.post(url,{name:name,thumbSuffix:thumbSuffix},function(result){
			if(result.status == 1){
				th.parents('ul').remove();
				var len = $(".image_up ul").length;
				$('.J_feng').eq(0).text('封面').css({color:"#FF9900"});
				if(len==0){
					$('#imgid').val('');
					$('#imgid').blur();
				}
				return false;
			} else {
				$.pinphp.tip({content:result.msg, icon:'error'});
				return false;
			}
		},'json');
	});
	
	
	//搜索楼盘
	$('#pin_business').keyup(function(){
		var business = $.trim($('#pin_business').val());
		var url   = PINER.root + '/?g=admin&m=property&a=pinpai&roleid=1';
		if(business!=''){
		$('#J_pinpai_name').show();
		$('#pin_id').val('');
		    $.post(url,{business:business},function(result){
				    if(result.status == 1){
					    $('#J_pinpai_name').html(result.data);
					    return false;
				    }else{
					    $.pinphp.tip({content:result.msg, icon:'error'});
					    return false;
				    }
				    
			    },'json');
		}else{
			
			$('#J_pinpai_name').hide();
		}
	});
	//选择
	$('#J_pinpai_name > ul > li').live('click',function(){
		var rel = $(this).attr('rel');
		var business = $(this).text();
		$('#pin_id').val(rel);
		$('#pin_business').val(business);
		$('#J_pinpai_name').hide();
	});
	
	//添加品牌
	$('.J_pinpai_add').click(function(){
		var pin_business = $('#pin_business').val();
		if(pin_business==''){
			$.pinphp.tip({content:'品牌名称不能为空', icon:'error'});
			return false;	
		}
		var url   = PINER.root + '/?g=admin&m=property&a=ajax_check_name&roleid=1';
		$.post(url,{business:pin_business},function(result){
			if(result.status == 1){
				$('#pin_id').val(result.data);
				$.pinphp.tip({content:'添加成功', icon:'success'});
				return false;
			}else{
				$.pinphp.tip({content:'重复楼盘请选择', icon:'error'});
				return false;
			}
			
		},'json');
		$('#J_pinpai_name').hide();	
	});
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
});