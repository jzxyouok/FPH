/*
*CH-L
*公共js
*20140407
*/
$(function(){
	//注册
	$('.J_sub_register').click(function(){
		//var username    = $.trim($('#username').val());
		var mobile      = $.trim($('#mobile').val());
		var mobile_code = $.trim($('#mobile_code').val());
		var password    = $.trim($('#password').val());
		var password2   = $.trim($('#password2').val());
		var dzp_url     = unescape($.trim($('#url').val()));
		var origin      = 1;//终端 1:微信 2:IOS 3:Android 4:PC
		var share_uid   = $.trim($('#share_uid').val());
		var reg_url     = PINER.root + '/?g=home&m=user&a=register';
		if(dzp_url==''){
			layer.msg('参数出错',2,3);
			return false;
		}
		if(mobile==''){
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'请输入手机号码',type : 3}	
			});
			return false;
		 }
		 if(!mobile.match(/^13[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[0123456789]{1}[0-9]{8}$|14[0-9]{1}[0-9]{8}$|17[0123456789]{1}[0-9]{8}$/)){ 
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'手机号码格式不正确！',type : 3}	
			});
			return false; 
		 } 
		 if(mobile_code=='' || mobile_code.length!=6){
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'验证码输入错误',type : 3}	
			});
			return false;
		 }
		 if(password=='' || password.length<6){
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'请输入不能小于6位的密码',type : 3}	
			});
			return false;
		 }
		 if(password2==''){
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'请输入确认密码',type : 3}	
			});
			return false;
		 }
		 if(password!=password2){
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'两次密码输入不一致',type : 3}	
			});
			return false;
		 }
		 $.post(reg_url,{mobile:mobile,mobile_code:mobile_code,password:password,password2:password2,share_uid:share_uid,origin:origin},function(result){
			if(result.status == 1){
				window.location.href=dzp_url; 
				return false;
            } else {
				layer.msg(result.msg,2,3);
				return false;
            }
		},'json');
	});
	
	//登录
	$('#submit_login').click(function(){
		var mobile      = $.trim($('#mobile').val());
		var password    = $.trim($('#password').val());
		var dzp_url     = unescape($.trim($('#url').val()));
		var login_url   = PINER.root + '/?g=weixin&m=user&a=login';
		if(dzp_url==''){
			layer.msg('参数出错',2,3);
			return false;
		}
		if(mobile==''){
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'请输入手机号号码',type : 3}	
			});
			return false;
		}
		 if(!mobile.match(/^13[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[0123456789]{1}[0-9]{8}$|14[0-9]{1}[0-9]{8}$|17[0123456789]{1}[0-9]{8}$/)){ 
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'手机号码格式不正确！请重新输入！',type : 3}	
			});
			return false; 
		 } 
		if(password==''){
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'请输入密码',type : 3}	
			});
			return false;
		}
		$.post(login_url,{mobile:mobile,password:password},function(result){
			if(result.status == 1){
				window.location.href=dzp_url; 
				return false;
            } else {
				layer.msg(result.msg,2,3);
				return false;
            }
		},'json');
	});
	
	//退出
	$('.J_logout').click(function(){
		var url = PINER.root + '/?g=home&m=user&a=logout';
		$.layer({
			shade: [0.2, '#000',true],
			area: ['auto','auto'],
			title: '提示信息',
			dialog: {
				msg: '您确定退出么？',
				btns: 2,                    
				type: 4,
				btn: ['确定','取消'],
				yes: function(){
					$.post(url,{},function(result){
						if(result.status == 1){
							layer.msg(result.msg, 2, 1);
							window.location.reload();
							return false;
						} else {
							layer.msg('退出失败',2,3);
							return false;
						}
					},'json');
				}
			}
		});
	});	
	
    //修改密码第一把步*发送验证码
	$('#sub_send_code').click(function(){
		var mobile      = $.trim($('#mobile').val());
		var mobile_code    = $.trim($('#mobile_code').val());
		var edit_pass_url   = PINER.root + '/?g=weixin&m=edit_pass&a=send_code';
		if(mobile==''){
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'请输入手机号号码',type : 3}	
			});
			return false;
		}
		 if(!mobile.match(/^13[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[0123456789]{1}[0-9]{8}$|14[0-9]{1}[0-9]{8}$|17[0123456789]{1}[0-9]{8}$/)){ 
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'手机号码格式不正确！请重新输入！',type : 3}	
			});
			return false; 
		 } 
		if(mobile_code==''){
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'请输入验证码',type : 3}	
			});
			return false;
		}
		$('#sub_send_code').val('正在提交...');
		$.post(edit_pass_url,{mobile:mobile,mobile_code:mobile_code},function(result){
			if(result.status == 1){
                var edit_pass_url = PINER.root + '/?g=weixin&m=edit_pass&a=edit_password&mobile='+result.data['mobile']+'&id='+result.data['id']+'';
				window.location.href=edit_pass_url;
				return false;
            } else {
				layer.msg(result.msg,2,3);
				$('#sub_send_code').val('下一步');
				return false;
            }
		},'json');
	});
        
    //修改密码
	$('#sub_edit_pass').click(function(){
		var password      = $.trim($('#password').val());
		var password2     = $.trim($('#password2').val());
        var mobile        = $.trim($('#mobile').val());
		var id            = $.trim($('#id').val());
		var edit_pass_url   = PINER.root + '/?g=weixin&m=edit_pass&a=edit_password';
		var weixin_user_index   = PINER.root + '/?g=weixin&m=user&a=index';
		if(password=='' || password.length<6){
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'请输入不能小于6位的密码',type : 3}	
			});
			return false;
		 }
         if(password2==''){
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'确认密码不能为空',type : 3}	
			});
			return false;
		}
		if(password2 != password){
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'两次密码不一致',type : 3}	
			});
			return false;
		}
		$('#sub_edit_pass').val('正在提交...');
		$.post(edit_pass_url,{password:password,password2:password2,mobile:mobile,id:id},function(result){
			if(result.status == 1){
				window.location.href=weixin_user_index;
				return false;
            } else {
				layer.msg(result.msg,2,3);
				$('#sub_edit_pass').val('确定修改');
				return false;
            }
		},'json');
	});
	//修改注册信息
	$('#edit_register').click(function(){
		var username    = $.trim($('#username').val());
		var mobile      = $.trim($('#mobile').val());
		var mobile_code = $.trim($('#mobile_code').val());
		var gender      = $('#gender').val();
		var address     = $.trim($('#address').val());
		var id          = $.trim($('#id').val());
		var edit_url    = PINER.root + '/?g=weixin&m=user&a=editmy';
		var user_index  = PINER.root + '/?g=weixin&m=user&a=index';
		//alert(id); return false;
		if(id==''){
			layer.msg('参数出错',2,3);
			return false;
		}
		if(username==''){
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'请输入昵称',type : 3}	
			});
			return false;
		}
		if(!username.match(/^[0-9a-zA-Z_\u3E00-\u9FA5]+$/)){ 
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'昵称只能包括中文、字母、数字和下划线！',type : 3}	
			});
			return false; 
		 } 
		if(mobile==''){
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'请输入手机号号码',type : 3}	
			});
			return false;
		}
		if(!mobile.match(/^13[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[0123456789]{1}[0-9]{8}$|14[0-9]{1}[0-9]{8}$|17[0123456789]{1}[0-9]{8}$/)){ 
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'手机号码格式不正确！请重新输入！',type : 3}	
			});
			return false; 
		} 
		if(gender!=1 && gender!=0){
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'请选择性别',type : 3}	
			});
			return false;
		}
		$.post(edit_url,{mobile:mobile,mobile_code:mobile_code,id:id,address:address,gender:gender},function(result){
			if(result.status == 1){
				window.location.href=user_index; 
				return false;
            } else {
				layer.msg(result.msg,2,3);
				return false;
            }
		},'json');
	});
	
	//修改性别
	$('.sexbox > a').click(function(){
		var rel = $(this).attr('rel');	
		$('#gender').val(rel);
		$('.sexbox_cur').removeClass('sexbox_cur');
		$(this).addClass('sexbox_cur');
	});
	
	//选择带看类型
	$('.J_select_look > a').click(function(){
		$('.cur').removeClass('cur');
		$(this).addClass('cur');
		var rel = $(this).attr('rel');
		$('#with_look').val(rel);
		if(rel == 1){			
			$('.choose_type2').hide();
		}else if(rel == 2){
			$('.choose_type2').show();
		}
	});
	
	//选择意向楼盘
	$('.J_select_loupan').click(function(){
		var propertyarr =[];    
		$('input[name="property"]').each(function(){    
			propertyarr.push($(this).val());    
		}); 
		$(".bgdiv").css({ display: "block", height: $(document).height() });
		$('.J_title').text('请选择意向楼盘');
		$('.J_but_close').html('<a href="javascript:;" class="tbn_submit pa J_click_sel">确定</a>');
		$('#J_list_data').html('<div style="text-align:center;padding-top:50px;"><img src="'+PINER.static+'/css/default/images/loading1.gif"/></div>');
		$('#bottom_div_data').slideDown();
		var url = PINER.root + '/?g=weixin&m=kehu&a=ajax_property';
		$.post(url,{propertyarr:propertyarr},function(result){
			if(result.status == 1){
				$('#J_list_data').html(result.data);
				return false;
            } else {
				close_layer();
				layer.msg(result.msg,2,3);
				return false;
            }
		},'json');
		
	});
	
	$('.J_click_sel').live('click',function(){
		var node_len = $('.node_len').length;
		if(node_len>0){
			$('.choose_result').show();
		}else if(node_len==0){
			$('.choose_result').hide();	
		}
		if(node_len>=3){
				
		}
		close_layer();
		return false;
	});
	
	var speeds = 250;
	//选择意向区域
	$('.J_select_city').click(function(){
		var area = $('#area').val();
		$('#J_title').text('我的区域');
		$('#J_title').attr('class','area');
		$('#J_list_data').empty().html('<div style="text-align:center;padding-top:50px;"><img src="'+PINER.static+'/css/default/images/loading1.gif"/></div>');
		var url = PINER.root + '/?g=weixin&m=kehu&a=ajax_city';
		$.post(url,{area:area},function(result){
			if(result.status==1){
				$('#J_list_data').html(result.data);
				$('.mobilePopup').fadeIn(speeds);
				return false;
			}else{
				layer.msg(result.msg,2,3);
				return false;	
			}
		},'json');
		
	});
        
    //会员资料 选择意向区域 省
	$('.J_edit_user_province').click(function(){
		$(".bgdiv").css({ display: "block", height: $(document).height() });
		$('.mobilePopup ul').html('<div><img src="'+PINER.static+'/css/default/weixin/images/loading1.gif" width="39" height="39" /></div>');
		$('.mobilePopup').fadeIn();
		var id = $('#province').val();
		$('.mobilePopup i').text('我的区域');
		$('.J_edit_my').attr('class','J_edit_my close')
		$('.J_edit_my').text('X')
		var url = PINER.root + '/?g=weixin&m=user&a=ajax_city';
		$.post(url,{id:id,name:1},function(result){
			if(result.status==1){
				$('.mobilePopup ul').html(result.data);
				return false;
			}else{
				layer.msg(result.msg,2,3);
				return false;	
			}
		},'json');
	});
        
	$('.J_edit_user_city').click(function(){
		if(id==''){
			layer.msg('选取顺序有误',2,3);
			return false;
		}
		$(".bgdiv").css({ display: "block", height: $(document).height() });
		$('.mobilePopup ul').html('<div><img src="'+PINER.static+'/css/default/weixin/images/loading1.gif" width="39" height="39" /></div>');
		$('.mobilePopup i').text('我的区域');
		$('.J_edit_my').attr('class','J_edit_my close')
		$('.J_edit_my').text('X')
		var id = $('#city').val();
		$('.mobilePopup').fadeIn();
		var url = PINER.root + '/?g=weixin&m=user&a=ajax_city';
		$.post(url,{id:id,name:2},function(result){
			if(result.status==1){
				$('.mobilePopup ul').html(result.data);
				return false;
			}else{
				layer.msg(result.msg,2,3);
				return false;	
			}
		},'json');
	});
		
	$('.J_edit_user_regional').click(function(){
		if(city==''){
			layer.msg('选取顺序有误',2,3);
			return false;
		}
		if(id==''){
			layer.msg('选取顺序有误',2,3);
			return false;
		}
		$(".bgdiv").css({ display: "block", height: $(document).height() });
		$('.mobilePopup ul').html('<div><img src="'+PINER.static+'/css/default/weixin/images/loading1.gif" width="39" height="39" /></div>');
		$('.mobilePopup').fadeIn();
		$('.mobilePopup i').text('我的区域');
		$('.J_edit_my').attr('class','J_edit_my close')
		$('.J_edit_my').text('X')
		var id = $('#regional').val();
		var city = $('#city').val();
		var url = PINER.root + '/?g=weixin&m=user&a=ajax_city';
		$.post(url,{id:id,name:3},function(result){
			if(result.status==1){
				$('.mobilePopup ul').html(result.data);
				return false;
			}else{
				layer.msg(result.msg,2,3);
				return false;	
			}
		},'json');
	});
	
	//选择擅长物业类型
	$('.J_editmy_s_wuye').click(function(){
		$(".bgdiv").css({ display: "block", height: $(document).height() });
		$('.mobilePopup ul').html('<div><img src="'+PINER.static+'/css/default/weixin/images/loading1.gif" width="39" height="39" /></div>');
		$('.mobilePopup').fadeIn();
		var id = $('#province').val();
		$('.mobilePopup i').text('擅长物业类型');
		$('.J_edit_my').attr('class','J_edit_my okay')
		$('.J_edit_my').text('确定')
		var url = PINER.root + '/?g=weixin&m=user&a=ajax_property_cate';
		var propertyarr =[];
		$('input[name="property_cate_id"]').each(function(){    
			propertyarr.push($(this).val());    
		}); 
		$.post(url,{propertyarr:propertyarr},function(result){
			if(result.status==1){
				$('.mobilePopup ul').html(result.data);
				return false;
			}else{
				layer.msg(result.msg,2,3);
				return false;	
			}
		},'json');
	});
	
	$('.J_select_wuye_li').live('click',function(){
		var name = $(this).text();
		var rel = $(this).attr('rel');
		var node_len = $('.node_len').length;
		if($('.property_id_'+rel+'').length>0){
			$(this).removeClass('on');
			$('.property_name_'+rel+'').remove();
			$('.property_id_'+rel+'').remove();
			return false;
		}
		/*
		if(node_len>=3){
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'最多只能添加三个!',type : 3}	
			});
			return false;	
		}*/
		$('#J_property_data').append('<input class="property_id_'+rel+'" type="hidden" name="property_cate_id" value="'+rel+'"/>');
		$('.longSel_content').append('<label class="property_name_'+rel+' node_len">'+name+'</label>');
		$(this).addClass('on');
		return false;
	});
	
	$('.okay').live('click',function(){
		var node_len = $('.node_len').length;
		if(node_len==0){
			$('.longSel_content').hide();
		}else{
			$('.longSel_content').show();
		}
	});
        
        //修改会员信息
        $('#edit_register_user').click(function(){
			var J_img = $('#J_img').val();
			var username = $('#username').val();
			var gender = $('#gender').val();
			var mobile = $('#mobile').val();
			var mobile_code = $('#mobile_code').val();
			var city_id = $('#get_city_id').val(); 
			var address = $('#address').val();
			var id = $('#id').val();
			var city = $('#cityid').val();
			var regional = $('#districtid').val();
			var property_cate_id = $('#property_cate_id').val();
			
			if(city!=''){
				if( regional=='' || city_id ==''){
					 $.layer({
						shade : [0.4 , '#000' , false],
						area : ['auto','auto'],
						title : false,
						closeBtn:false,
						time : 2,
						dialog : {msg:'我的区域 选项不能为空！',type : 3}	
					});
					return false; 
                        
                }  
			}
				   
			var url = PINER.root + '/?g=weixin&m=user&a=editmy';
			var user_index = PINER.root + '/?g=weixin&m=user&a=index';
			$('#edit_register_user').text('提交中...');
			$.post(url,{J_img:J_img,username:username,gender:gender,mobile:mobile,mobile_code:mobile_code,city_id:city_id,address:address,id:id,city:city,regional:regional,property_cate_id:property_cate_id},function(result){
				if(result.status==1){
					window.location.href=user_index;
					return false;
				}else{
					layer.msg(result.msg,2,3);
					return false;	
				}
			},'json');
	});

	
	//选择价格区间
	$('.J_select_price').click(function(){
		var price = $('#price').val();
		$('#J_title').text('客户选择意价格');
		$('#J_title').attr('class','price');
		$('#J_list_data').html('<div style="text-align:center;padding-top:50px;"><img src="'+PINER.static+'/css/default/images/loading1.gif"/></div>');
		var url = PINER.root + '/?g=weixin&m=kehu&a=ajax_price';
		$.post(url,{price:price},function(result){
			if(result.status==1){
				$('#J_list_data').html(result.data);
				$('.mobilePopup').fadeIn(speeds);
				return false;
			}else{
				layer.msg(result.msg,2,3);
				return false;
			}
		},'json');
		
	});
	
	$('.J_click_area').live('click',function(){
		var name = $(this).text();
		var rel = $(this).attr('rel');
		$('#J_area_data').text(name);
		$('#area').val(rel);
		close_layer();
		return false;
	});
	
	$('.J_click_price').live('click',function(){
		var name = $(this).text();
		var rel = $(this).attr('rel');
		$('#J_price_data').text(name);
		$('#price').val(rel);
		close_layer();
		return false;
	});
	
	$('.J_click_property').live('click',function(){
		var name = $(this).text();
		var rel = $(this).attr('rel');
		var node_len = $('.node_len').length;
		if($('.property_id_'+rel+'').length>0){
			$(this).removeClass('cur');
			$('.property_name_'+rel+'').remove();
			$('.property_id_'+rel+'').remove();
			return false;
		}
		if(node_len>=3){
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'最多只能添加三个楼盘!',type : 3}	
			});
			return false;	
		}
		$('#J_property_data').append('<input class="property_id_'+rel+'" type="hidden" name="property" value="'+rel+'"/>');
		$('.choose_result').append('<span class="property_name_'+rel+' node_len">'+name+'</span>');
		$(this).addClass('cur');
		return false;
	});
	
	//我要推客
	$('#J_kehu_add_submit').live('click',function(){
		var name = $.trim($('#name').val());
		var mobile = $.trim($('#mobile').val());
		var area = $.trim($('#area').val());
		var price = $.trim($('#price').val());
		if(!name.match(/^[a-zA-Z\u3E00-\u9FA5]+$/)){ 
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'客户姓名只能是中文或英文',type : 3}	
			}); 
			return false; 
		} 

		if(!mobile.match(/^13[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[0123456789]{1}[0-9]{8}$|14[57]{1}[0-9]$|17[0123456789]{1}[0-9]{8}$/)){ 
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'手机号码填写错误！',type : 3}	
			}); 
			return false; 
		}
		if(area==''){
			 $.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'请选择意向区！',type : 3}	
			});
			return false; 
		}
		if(price==''){
			 $.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'请选择意向价格！',type : 3}	
			});
			return false; 
		}
		$('#J_kehu_add_submit').text('正在提交...');
		var url = PINER.root + '/?g=weixin&m=kehu&a=add';
		var kehu_index = PINER.root + '/?g=weixin&m=kehu&a=index';
		$.post(url,{name:name,mobile:mobile,area:area,price:price},function(result){
			if(result.status==1){
				window.location.href=kehu_index; 
				return false;
			}else{
				layer.msg(result.msg,2,3);
				$('#J_kehu_add_submit').text('确认添加');
				return false;	
			}
		},'json');
		 
		
	});
	//提交添加报备类型
	$('.J_baobei_type_submit').live('click',function(){
		var property = $.trim($('#property').val());
		var uid = $.trim($('#uid').val());
		var with_look = $.trim($('#with_look').val());
                if(property==''){			
                        layer.msg('请选择报备楼盘',2,3);
			return false; 
		}
                if(uid==''){			
                        layer.msg('参数出错',2,3);
			return false; 
		}
                if(with_look==''){			
                        layer.msg('请选择带看类型',2,3);
			return false; 
		}
		
	});
	//提交添加报备
	$('.J_baobei_add_submit').live('click',function(){
		var name = $.trim($('#name').val());
		var mobile = $.trim($('#mobile').val());
		var visit_time = $.trim($('#visit_time').val());
                var property = $.trim($('.property').val());
                var uid = $.trim($('.uid').val());
                var with_look = $.trim($('.with_look').val());
                var gender = $.trim($('.gender').val());
		if(!name.match(/^[a-zA-Z\u3E00-\u9FA5]+$/)){ 
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'客户姓名只能是中文或英文',type : 3}	
			});
			return false; 
		} 
		
		if(!mobile.match(/^13[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[0123456789]{1}[0-9]{8}$|14[57]{1}[0-9]$|17[0123456789]{1}[0-9]{8}$/)){ 
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'手机号码填写错误！',type : 3}	
			});
			return false; 
		}
 
                if(with_look==1 && visit_time==''){
			 $.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'请选择日期！',type : 3}	
			});
			return false; 
		}

                if(property=='' || uid=='' || with_look==''){
			 $.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'数据信息有误！',type : 3}	
			});
			return false; 
		}
  
                if(gender==''){
			 $.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'性别不能为空！',type : 3}	
			});
			return false; 
		}

            var url = PINER.root + '/?g=weixin&m=baobei&a=add';
            $('.J_baobei_add_submit').val('正在提交');
            $.post(url,{name:name,mobile:mobile,visit_time:visit_time,property:property,uid:uid,with_look:with_look,gender:gender},function(result){
			if(result.status == 1){
				window.location.href=PINER.root + '/?g=weixin&m=baobei&a=add'+'&title='+result.data['title']+'&daikanstr='+result.data['daikanstr']; 
				return false;
            } else {
				layer.msg(result.msg,2,3);
                                $('.J_baobei_add_submit').val('下一步');
				return false;
            }
		},'json');
                
		
	});
    //报备完成跳转页面
	$('.baobeiurl').click(function(){
		window.location.href=PINER.root + '/?g=weixin&m=loupan&a=index';
	});
    
	//我的客户页面 点击类型
	$('#a_type').click(function(){
		if($(".j_show1").is(":hidden"))
		{
			$('.j_show1').show();
			$('.j_show2').hide();
		}
		else
		{
			$('.j_show1').hide();
		}
	});
	
	//我的客户页面 点击状态
	$('#a_state').click(function(){
		if($(".j_show2").is(":hidden"))
		{
			$('.j_show2').show();
			$('.j_show1').hide();
		}
		else
		{	
			$('.j_show2').hide();
		}
	});
        
        //合作楼盘 区域
	$('#a_city').click(function(){
		$(".bgdiv").css({ display: "block", height: $(document).height() });
		if($(".j_show1").is(":hidden"))
		{
			$('.j_show1').show();
			$('.j_show2').hide();
            $('.j_show3').hide();
		}
		else
		{	
			$(".bgdiv").hide();
			$('.j_show1').hide();
		}
	});
         //合作楼盘 开盘时间
	$('#a_kaipan').click(function(){
		$(".bgdiv").css({ display: "block", height: $(document).height() });
		if($(".j_show2").is(":hidden"))
		{
			$('.j_show1').hide();
			$('.j_show2').show();
            $('.j_show3').hide();
		}
		else
		{	
			$(".bgdiv").hide();
			$('.j_show2').hide();
		}
	});
        
        //合作楼盘 单价排序
	$('#a_order').click(function(){
		$(".bgdiv").css({ display: "block", height: $(document).height() });
		if($(".j_show3").is(":hidden"))
		{
			$('.j_show1').hide();
			$('.j_show2').hide();
            $('.j_show3').show();
		}
		else
		{	
			$(".bgdiv").hide();
			$('.j_show3').hide();
		}
	});
	
	//添加意向楼盘
	$('.J_select_loupan_add').click(function(){
		var propertyarr =[];    
		$('input[name="property"]').each(function(){    
			propertyarr.push($(this).val());    
		});
		var id = $('#id').val();
		if(id==''){
			layer.msg('非法参数',2,3);
			return false;	
		}
		$(".bgdiv").css({ display: "block", height: $(document).height() });
		$('.J_title').text('请选择意向楼盘');
		$('.J_but_close').html('<a href="javascript:;" class="tbn_submit pa J_click_sel_add">确定</a>');
		$('#J_list_data').html('<div style="text-align:center;padding-top:50px;"><img src="'+PINER.static+'/css/default/images/loading1.gif"/></div>');
		$('#bottom_div_data').slideDown();
		var url = PINER.root + '/?g=weixin&m=kehu&a=ajax_property_add';
		$.post(url,{propertyarr:propertyarr,id:id},function(result){
			if(result.status == 1){
				$('#J_list_data').html(result.data);
				return false;
            } else {
				close_layer();
				layer.msg(result.msg,2,3);
				return false;
            }
		},'json');
	});
	
	$('.J_click_sel_add').live('click',function(){
		var property =[];    
		$('input[name="property"]').each(function(){    
			property.push($(this).val());    
		});
		var id = $('#id').val();
		if(id==''){
			layer.msg('非法参数',2,3);
			return false;	
		}
		if(property==''){
			close_layer();
			return false;	
		}
		var url = PINER.root + '/?g=weixin&m=kehu&a=detail_ajax_property_add';
		$.post(url,{property:property,id:id},function(result){
			if(result.status == 1){
				close_layer();
				window.location.reload()
				return false;
            } else {
				close_layer();
				layer.msg(result.msg,2,3);
				return false;
            }
		},'json');
	});
	
	$('.J_click_property_add').live('click',function(){
		var name = $(this).text();
		var rel = $(this).attr('rel');
		var node_len = $('.node_len').length;
		if($('.property_id_'+rel+'').length>0){
			$(this).removeClass('cur');
			$('.property_name_'+rel+'').remove();
			$('.property_id_'+rel+'').remove();
			return false;
		}
		if(node_len>=3){
			$.layer({
				shade : [0.4 , '#000' , false],
				area : ['auto','auto'],
				title : false,
				closeBtn:false,
				time : 2,
				dialog : {msg:'最多只能添加三个楼盘!',type : 3}	
			});
			return false;	
		}
		$('#J_property_data').append('<input class="property_id_'+rel+' node_len" type="hidden" name="property" value="'+rel+'"/>');
		$(this).addClass('cur');
		return false;
	});
	
	//修改意向区域
	$('.J_sel_yx_area').live('click',function(){
		$(".bgdiv").css({ display: "block", height: $(document).height() });
		$('.J_title').text('请选择意向区域');
		$('.J_but_close').html('<a href="javascript:;" onClick="close_layer();" class="tbn_closed pa">关闭</a>');
		$('#J_list_data').html('<div style="text-align:center;padding-top:50px;"><img src="'+PINER.static+'/css/default/images/loading1.gif"/></div>');
		$('#bottom_div_data').slideDown();
		var url = PINER.root + '/?g=weixin&m=kehu&a=edit_ajax_city';
		$.post(url,{},function(result){
			if(result.status==1){
				
				$('#J_list_data').html(result.data);
				
				return false;
				
			}else{
				layer.msg(result.msg,2,3);
				return false;	
			}
		},'json');
	});
	
	$('.J_click_area_edit').live('click',function(){
		var id = $('#id').val();
		var area_id = $(this).attr('rel');
		var area_name = $(this).text();
		if(id=='' || area_id=='' || area_name==''){
			layer.msg('非法参数',2,3);
			return false;	
		}
		$('.J_yixiang_area').text(area_name);
		$('.J_sel_yx_area').removeClass();
		close_layer();
		var url = PINER.root + '/?g=weixin&m=kehu&a=inster_ajax_city';
		$.post(url,{id:id,area_id:area_id},function(result){
			if(result.status==1){
				return false;
			}else{
				layer.msg(result.msg,2,3);
				return false;	
			}
		},'json');
	});
	
	//修改价格区间
	$('.J_sel_yx_price').live('click',function(){
		$(".bgdiv").css({ display: "block", height: $(document).height() });
		$('.J_title').text('请选择意向价格范围');
		$('.J_but_close').html('<a href="javascript:;" onClick="close_layer();" class="tbn_closed pa">关闭</a>');
		$('#J_list_data').html('<div style="text-align:center;padding-top:50px;"><img src="'+PINER.static+'/css/default/images/loading1.gif"/></div>');
		$('#bottom_div_data').slideDown();
		var url = PINER.root + '/?g=weixin&m=kehu&a=edit_ajax_price';
		$.post(url,{},function(result){
			if(result.status==1){
				$('#J_list_data').html(result.data);
				return false;
			}else{
				layer.msg(result.msg,2,3);
				return false;
			}
		},'json');
	});
	
	$('.J_click_price_edit').live('click',function(){
		var id = $('#id').val();
		var price_id = $(this).attr('rel');
		var price_name = $(this).text();
		if(id=='' || price_id=='' || price_name==''){
			layer.msg('非法参数',2,3);
			return false;	
		}
		$('.J_yixiang_price').text(price_name);
		$(this).parent().removeClass('a_link_newpage');
		$('.J_sel_yx_price').removeClass();
		close_layer();
		var url = PINER.root + '/?g=weixin&m=kehu&a=inster_ajax_price';
		$.post(url,{id:id,price_id:price_id},function(result){
			if(result.status==1){
				return false;
			}else{
				layer.msg(result.msg,2,3);
				return false;	
			}
		},'json');
	});
	
	//收藏楼盘
	$('.J_favorite').live('click',function(){
		var pid = $('#favorite').val();
		var uid = $('#uid').val();	
		var count = parseInt($('.aR i').text())+1;
		var return_url = $.trim($('#return_url').val());
		var url = PINER.root + '/?g=weixin&m=loupan&a=favorite';
		var th = $(this);
		if(uid==''){
			$.layer({
				shade : [0 , '#ffffff' , true],
				area : ['auto','auto'],
				dialog : {
					msg:'登录之后才能收藏',
					btns : 2, 
					type : 8,
					btn : ['确定','取消'],
					yes : function(){
						window.location.href=PINER.root + '/?g=weixin&m=user&a=login&url='+return_url; 
						return false;
					}
				}
			});	
			return false;
		}	
		if(pid==''){
		layer.msg('参数出错',2,3);
			return false;
		}
		$.post(url,{pid:pid},function(result){
				if(result.status == 1){
				    $('.aR i').text(count);
				    th.attr('class','favorite off J_favorites');
				    th.html('取消');
			        $.layer({
						shade : [0.4 , '#000' , false],
						area : ['auto','auto'],
						title : false,
						closeBtn:false,
						time : 2,
						dialog : {msg:result.msg,type : 1}	
					});
			return false;
		} else {
			 layer.msg(result.msg,2,3);
			   	return false;
			}
		},'json');
				
	});
	//取消收藏楼盘
	$('.J_favorites').live('click',function(){
		var pid = $('#favorite').val();
		var url = PINER.root + '/?g=weixin&m=loupan&a=cancel_favorite';
		var count = parseInt($('.aR i').text())-1;
		var th = $(this);
		if(pid==''){
		layer.msg('参数出错',2,3);
			return false;
		}
		$.post(url,{pid:pid},function(result){
				if(result.status == 1){
				   th.attr('class','favorite J_favorite');
				   $('.aR i').text(count);
				   th.html('收藏');
			       $.layer({
						shade : [0.4 , '#000' , false],
						area : ['auto','auto'],
						title : false,
						closeBtn:false,
						time : 2,
						dialog : {msg:result.msg,type : 1}	
					});
			return false;
		} else {
			 layer.msg(result.msg,2,3);
			   	return false;
			}
		},'json');
				
	});
	//由我带看 
        $(document).ready(function(){
            $('#detail_info').html('选择由我带看，当开发商确认客户有效性后，您必须亲自将客户带到案场，并与我们的案场工作人员当面确认带看。由我带看的佣金较高。');
            $("#with_look").val(1);
        });
	$('#youwodaikan').click(function(){
		$('#detail_info').html('选择由我带看，当开发商确认客户有效性后，您必须亲自将客户带到案场，并与我们的案场工作人员当面确认带看。由我带看的佣金较高。');
                $("#youwodaikan").attr("class",'on');
                $("#weituodaikan").attr("class",'');
                $("#with_look").val(1);
                
	});   
        // 委托带看
	$('#weituodaikan').click(function(){
		$('#detail_info').html('选择委托带看后，您可以不必亲自将客户带到案场，而由我们的工作人员邀约客户到访看房。委托带看的佣金较少。');
                $("#weituodaikan").attr("class",'on');
                $("#youwodaikan").attr("class",'');
                 $("#with_look").val(2);
	});   
	
	

//***	
})


//添加客户弹出层-关闭
function close_layer(){
	$('#bottom_div_data').slideUp();
	$(".bgdiv").css({ display: "none"});	
}
//跳转
function MM_jumpMenu(targ,selObj,restore){ 
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
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

