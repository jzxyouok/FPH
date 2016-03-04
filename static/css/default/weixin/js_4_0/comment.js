/*
*CH-L
*公共js*供微信端使用
*20150114
*/
$(function(){
	//注册
	$('.J_sub_register').click(function(){
		var mobile      = $.trim($('#mobile').val());
		var mobile_code = $.trim($('#mobile_code').val());
		var password    = $.trim($('#password').val());
		var password2   = $.trim($('#password2').val());
		//var company     = $.trim($('#company').val());
		//var stores_id   = $.trim($('#stores').val());
		var code_id     = $.trim($('#code_id').val());
		var return_url  = unescape($.trim($('#url').val()));
		var origin      = 1;//终端 1:微信 2:IOS 3:Android 4:PC
		var url         = PINER.root + '/?g=home&m=user&a=register';
		var th          = $(this);
		if(return_url==''){
			layer.open({
				content: '参数出错',
				style: '',
				time: 1,
				shade:true, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		 if(mobile==''){
			layer.open({
				content: '请输入手机号码',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		 }
		 if(!mobile.match(mobile_regex)){ 
			layer.open({
				content: '手机号码格式不正确!',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false; 
		 } 
		 if(mobile_code=='' || mobile_code.length!=6){
			layer.open({
				content: '验证码输入错误',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		 }
		 if(password=='' || password.length<6){
			layer.open({
				content: '请输入不能小于6位的密码',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		 }
		 if(password2==''){
			layer.open({
				content: '请输入确认密码',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		 }
		 if(password!=password2){
			layer.open({
				content: '两次密码输入不一致',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		 }
		 /*
		 if(company=='0' && code_id==''){
			layer.open({
				content: '请选择公司或者输入门店代码',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		 }
		 if(company!='0' && stores_id=='0'){
			layer.open({
				content: '请选择门店',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		 }
		 */
		 th.removeClass('J_sub_register');
		 th.text('正在提交...');
		 $.post(url,{mobile:mobile,mobile_code:mobile_code,password:password,password2:password2,origin:origin,code_id:code_id},function(result){
			if(result.status == 1){
				window.location.href=return_url; 
				return false;
            } else {
				th.addClass('J_sub_register');
		 		th.text('注册');
				layer.open({
					content: result.msg,
					style: '',
					time: 1,
					shade:true, //遮罩层 true,false
					anim:true //是否动画弹出 true false
				});
				return false;
            }
		},'json');
	});
	


	//注册选择公司
	$('.J_company').change(function(){
		var th  = $(this);
		var id  = th.val();
		var url = PINER.root + '/?g=weixin&m=user&a=ajax_stores';
		if(id!=0){
			$('.J_stores').show();
			th.parent('li').removeClass('noborder');
			$.post(url,{id:id},function(result){
				if(result.status == 1){
					var html = '<option value="0">选择门店</option>';
					$.each(result.data,function(i,n){
						html += '<option value="' + n['id'] + '">' + n['name'] + '</option>';
						$('.J_stores_option').html(html);
					 });
				} else {
					layer.open({
						content: result.msg,
						style: '',
						time: 1,
						shade:true,
						anim:true
					});
					return false;
				}
			},'json');
		}else{
			$('.J_stores').hide();
			$('.J_stores_option').html('<option value="0">选择门店</option>');
			th.parent('li').addClass('noborder');
		}
	});
	
	
	//登录*需要密码的登录
	$('.J_sub_login').click(function(){
		var mobile      = $.trim($('#mobile').val());
		var password    = $.trim($('#password').val());
		var return_url  = unescape($.trim($('#url').val()));
		var url         = PINER.root + '/?g=home&m=user&a=login';
		var th          = $(this);
		if(return_url==''){
			layer.open({
				content: '参数出错',
				style: '',
				time: 1,
				shade:true, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		if(mobile==''){
			layer.open({
				content: '请输入手机号码',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		if(!mobile.match(mobile_regex)){ 
			layer.open({
				content: '手机号码格式不正确！请重新输入！',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false; 
		} 
		if(password==''){
			layer.open({
				content: '请输入密码',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		th.removeClass('J_sub_login');
		th.text('正在登录...');
		$.post(url,{mobile:mobile,password:password},function(result){
			if(result.status == 1){
				window.location.href=return_url; 
				return false;
            } else {
				th.addClass('J_sub_login');
				th.text('登录');
				layer.open({
					content: result.msg,
					style: '',
					time: 2,
					shade:true, //遮罩层 true,false
					anim:true //是否动画弹出 true false
				});
				return false;
            }
		},'json');
	});

	//登录*不需要密码*直接验证手机
	$('.J_send_code_login').click(function(){
		var mobile      = $.trim($('#mobile').val());
		var mobile_code = $.trim($('#mobile_code').val());
		var admin_id    = $.trim($('#admin_id').val());
		var return_url  = unescape($.trim($('#url').val()));
		var url         = PINER.root + '/?g=weixin&m=user&a=login';
		var th          = $(this);
		if(return_url==''){
			layer.open({
				content: '参数出错',
				style: '',
				time: 1,
				shade:true,
				anim:true
			});
			return false;
		}
		if(mobile==''){
			layer.open({
				content: '请输入手机号码',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false;
		}
		if(!mobile.match(mobile_regex)){ 
			layer.open({
				content: '手机号码格式不正确！请重新输入！',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false; 
		} 
		if(mobile_code==''){
			layer.open({
				content: '请输入验证码',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false;
		}
		th.removeClass('J_sub_login');
		th.text('正在登录...');
		$.post(url,{mobile:mobile,mobile_code:mobile_code,admin_id:admin_id},function(result){
			if(result.status == 1){
				window.location.href=return_url; 
				return false;
            } else {
				th.addClass('J_sub_login');
				th.text('登录');
				layer.open({
					content: result.msg,
					style: '',
					time: 2,
					shade:true, //遮罩层 true,false
					anim:true //是否动画弹出 true false
				});
				return false;
            }
		},'json');
	});
	
	//退出
	$('.J_logout').click(function(){
		var url = PINER.root + '/?g=home&m=user&a=logout';
		layer.open({
			content: '你确定要退出登录?',
			btn: ['确认', '取消'],
			shadeClose: true,
			yes: function(){
				$.post(url,{},function(result){
					if(result.status == 1){
						layer.open({
							content: result.msg,
							style: '',
							time: 1,
							shade:false, //遮罩层 true,false
							anim:true //是否动画弹出 true false
						});
						window.location.reload();
						return false;
					} else {
						layer.open({
							content: '退出失败',
							style: '',
							time: 1,
							shade:false, //遮罩层 true,false
							anim:true //是否动画弹出 true false
						});
						return false;
					}
				},'json');
			}
		});
	});	
	
    //发送验证码
	$('.J_sub_send_code').click(function(){
		var mobile       = $.trim($('#mobile').val());
		var mobile_code  = $.trim($('#mobile_code').val());
		var url          = PINER.root + '/?g=weixin&m=password&a=index';
		if(mobile==''){
			layer.open({
				content: '请输入手机号号码',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		 if(!mobile.match(mobile_regex)){ 
			layer.open({
				content: '手机号码格式不正确！请重新输入！',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false; 
		 } 
		if(mobile_code==''){
			layer.open({
				content: '请输入验证码',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		$('.J_sub_send_code').html('正在提交...');
		$.post(url,{mobile:mobile,mobile_code:mobile_code},function(result){
			if(result.status == 1){
                var edit_pass_url = PINER.root + '/?g=weixin&m=password&a=password&mobile='+result.data['mobile']+'&id='+result.data['id']+'';
				window.location.href=edit_pass_url;
				return false;
            } else {
				layer.open({
					content: result.msg,
					style: '',
					time: 1,
					shade:true, //遮罩层 true,false
					anim:true //是否动画弹出 true false
				});
				$('.J_sub_send_code').html('下一步');
				return false;
            }
		},'json');
	});
        
    //修改密码
	$('.J_sub_edit_pass').click(function(){
		var password      = $.trim($('#password').val());
		var password2     = $.trim($('#password2').val());
        var mobile        = $.trim($('#mobile').val());
		var id            = $.trim($('#id').val());
		var edit_pass_url   = PINER.root + '/?g=weixin&m=password&a=password';
		var weixin_user_index   = PINER.root + '/?g=weixin&m=user&a=index';
		if(password=='' || password.length<6){
			layer.open({
				content: '请输入不能小于6位的密码',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		 }
         if(password2==''){
			layer.open({
				content: '确认密码不能为空',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		if(password2 != password){
			layer.open({
				content: '两次密码不一致',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		$('.J_sub_edit_pass').html('正在提交...');
		$.post(edit_pass_url,{password:password,password2:password2,mobile:mobile,id:id},function(result){
			if(result.status == 1){
				layer.open({
					content: result.msg,
					btn: ['确认'],
					shadeClose: true,
					yes: function(){
						window.location.href=weixin_user_index;
					}
				});
				return false;
            } else {
				layer.open({
					content: result.msg,
					style: '',
					time: 1,
					shade:true, //遮罩层 true,false
					anim:true //是否动画弹出 true false
				});
				$('.J_sub_edit_pass').html('确定修改');
				return false;
            }
		},'json');
	});
	
	
	// //新建门店 开始
	$('.stores_city_c').live('click',function(){
		if($('#get_city_id').val()==''){
			layer.open({
				content: '区域模板不能为空',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
		    return false;
		}
		if($('#stores_address').val()==''){
			layer.open({
				content: '地址不能为空',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		if($('#contact').val()==''){
			layer.open({
				content: '联系人不能为空',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		if($('#contact_tel').val()==''){
			layer.open({
				content: '联系人电话不能为空',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		if(!$('#contact_tel').val().match(mobile_regex)){
			layer.open({
				content: '手机号码格式不正确',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false; 
	 	} 
	    var th = $(this);
	    var  parent = th.parents('.steps');
	    parent.hide();
	    parent.next('.steps').fadeIn(300);
	    return false;
 });
 //新建门店块
  var stores_c_ok = $('.stores_c').not('.cancel');
  var stores_sc_ok = $('.stores_sc_b').not('.cancel');
  var stores_c_c_ok = $('.stores_c_c').not('.cancel');
  var stores_c_cancel = $('.stores_c.cancel');
  //添加门店
  stores_sc_ok.click(function(){
	if($('#get_one_company').val()==''){
		layer.open({
			content: '所属公司不能为空',
			style: '',
			time: 1,
			shade:false, //遮罩层 true,false
			anim:true //是否动画弹出 true false
		});
	    return false;
	}
    var th = $(this);
    var get_one_company = $.trim($('#get_one_company').val());
    var stores_name      = $.trim($('#stores_name').val());
    var get_city_id = $('#get_city_id').val();
    var stores_address = $('#stores_address').val();
	var contact = $('#contact').val();
    var contact_tel = $('#contact_tel').val();
    if($('#contact').val()==''){
		layer.open({
			content: '联系人不能为空',
			style: '',
			time: 1,
			shade:false, //遮罩层 true,false
			anim:true //是否动画弹出 true false
		});
		return false;
	}
	if($('#contact_tel').val()==''){
		layer.open({
			content: '联系人电话不能为空',
			style: '',
			time: 1,
			shade:false, //遮罩层 true,false
			anim:true //是否动画弹出 true false
		});
		return false;
	}
	if(!$('#contact_tel').val().match(mobile_regex)){
		layer.open({
			content: '手机号码格式不正确',
			style: '',
			time: 1,
			shade:false, //遮罩层 true,false
			anim:true //是否动画弹出 true false
		});
		return false; 
 	} 
    company_c_url = PINER.root + '/?g=weixin&m=stores&a=insert';
    $.post(company_c_url, { get_city_id:get_city_id, get_one_company:get_one_company,stores_name:stores_name,contact:contact,contact_tel:contact_tel,stores_address:stores_address,type:2,
      },function(result){
      if(result.status == 1){
        window.location.href= PINER.root + '/?g=weixin&m=stores&a=complete&type_ok=1&id='+result.data;
        return false;
      }
    },'json');
  });

  //检测门店
  stores_c_ok.click(function(e){
    var stores_c_url   = PINER.root + '/?g=weixin&m=stores&a=ajax_check_name';
    var th = $(this);
    var stores_name      = $.trim($('#stores_name').val());
    if(stores_name==''){
		layer.open({
			content: '门店名称不能为空',
			style: '',
			time: 1,
			shade:false, //遮罩层 true,false
			anim:true //是否动画弹出 true false
		});
		return false;
    }
    $.post(stores_c_url,{stores_name:stores_name},function(result){
      if(result.status == 1){
        var  parent = th.parents('.steps');
        parent.hide();
        parent.next('.steps').fadeIn(300);
        return false;
      }else{
      	if(result.data['teamwork'] ==0){
			layer.open({
				content: '该门店为保护门店',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
      	}
		layer.open({
			content: '新建的门店已存在,无需新建,是否进入该门店?',
			btn: ['确认', '取消'],
			shadeClose: false,
			yes: function(){
				layer.open({
					content: result.msg,
					style: '',
					time: 1,
					shade:false, //遮罩层 true,false
					anim:true //是否动画弹出 true false
				});
				window.location.href= PINER.root + '/?g=weixin&m=stores&a=complete&type_ok=2&id='+result.data['result'];
			}
		});
        return false;
      }
    },'json');
  });
//门店所属公司
  stores_c_c_ok.click(function(e){
    var company_c_url   =  PINER.root + '/?g=weixin&m=stores&a=ajax_check_company';
    var th = $(this);
    var company_name      = $.trim($('#company_name').val());
    if(company_name==''){
		layer.open({
			content: '所属公司不能为空',
			style: '',
			time: 1,
			shade:false, //遮罩层 true,false
			anim:true //是否动画弹出 true false
		});
      return false;
     }
     th.removeClass('stores_c_c');
	 th.text('正在提交...');
     $.post(company_c_url,{company_name:company_name},function(result){
      if(result.status == 0){
        var  parent = th.parents('.steps');
        parent.hide();
        parent.next('.steps').fadeIn(300);
        //查找对应所属公司
        var company_cc_url = PINER.root + '/?g=weixin&m=stores&a=ajax_check_company_list';
        $.post(company_cc_url,
          {
            company_name:company_name,
          },function(result){
            
          if(result.status == 1){
            $('.stores_count').html(result.data['count']);
            $('.stores_c_s').html(result.data['str']);
            return false;
          }
        },'json');
        return false;
      }else{

        var stores_name = $('#stores_name').val();
        var stores_address = $('#stores_address').val();
        var contact = $('#contact').val();
        var contact_tel = $('#contact_tel').val();
        var get_city_id = $('#get_city_id').val();
        if($('#contact').val()==''){
			layer.open({
				content: '联系人不能为空',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		if($('#contact_tel').val()==''){
			layer.open({
				content: '联系人电话不能为空',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		if(!$('#contact_tel').val().match(mobile_regex)){
			layer.open({
				content: '手机号码格式不正确',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false; 
	 	}
		layer.open({
			title: [
				'是否新建公司',
				'background-color:#8DCE16;color:#fff;'
			],
			shadeClose: false,
			btn: ['确认', '取消'],
			content: '无法在数据库中找到该公司<br>可能该公司尚未建立',
			yes: function(){
				company_c_url = PINER.root + '/?g=weixin&m=stores&a=insert';
				$.post(company_c_url,{get_city_id:get_city_id,company_name:company_name,stores_name:stores_name,stores_address:stores_address,contact:contact,contact_tel:contact_tel,type:1,},function(result){
					if(result.status == 1){
						layer.open({
							content: result.msg,
							style: '',
							time: 1,
							shade:false, //遮罩层 true,false
							anim:true //是否动画弹出 true false
						});
						window.location.href = PINER.root + '/?g=weixin&m=stores&a=complete&type_ok=1&id='+result.data;
						return false;
					}else{
						th.addClass('stores_c_c');
						th.text('确定');
						layer.open({
							content: result.msg,
							style: '',
							time: 1,
							shade:true, //遮罩层 true,false
							anim:true //是否动画弹出 true false
						});
						return false;
					}
				},'json');
			}, no: function(){
				th.addClass('stores_c_c');
	 			th.text('确定');
			}
		});
		
		
        return false;
      }
    },'json');
  });

$('.stores_get_add').click(function(){

	var stores_c_url   =  PINER.root + '/?g=weixin&m=stores&a=ajax_check_code_id';
	var code_id        = $.trim($('#code_id').val());
    if(code_id==''){
		layer.open({
			content: '请输入门店邀请码',
			style: '',
			time: 1,
			shade:false, //遮罩层 true,false
			anim:true //是否动画弹出 true false
		});
		return false;
    }
	$.post(stores_c_url,{code_id:code_id},function(result){
			if(result.status == 0){//已存在
				layer.open({
					content: '是否加入该门店<br>'+result.data['name'],
					btn: ['确认', '取消'],
					shadeClose: false,
					yes: function(){
						window.location.href = PINER.root + '/?g=weixin&m=stores&a=store_required&id='+result.data['id'];
						return false;
					}
				});
                return false;
            } else {
				layer.open({
					content: '找不到该门店<br>请确认您的门店代码是否填写正确，或联系客服400-886-9058',
					btn: ['确认'],
					shadeClose: false,
					yes: function(){
						window.location.reload();
						return false;
					}
				});
				return false;
            }
		},'json');

});
$('.stores_c_s li a').live('click',function(){ 
    $('#get_one_company').val($(this).attr('rel'));
    $('.stores_c_s li a').removeClass('checked');
    $(this).addClass('checked');
  });
  stores_c_cancel.click(function(e){
    var $this = $(this);
    var $parent = $this.parents('.steps');
    $parent.hide();
    $parent.prev('.steps').fadeIn(300);
  });

	//新建门店结束
//门店日志信息录入
	$('.store_log').click(function(){
		var url = PINER.root + '/?g=weixin&m=stores&a=store_log_insert';
		var store_info      = $.trim($('#store_info').val());
		var store_id      = $.trim($('#store_id').val());
		 if(store_info==''){
			layer.open({
				content: '请填写跟进内容',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		 }
		 if(store_id==''){
			layer.open({
				content: '参数有误',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		 }
		 $.post(url,{store_info:store_info,store_id:store_id},function(result){

			if(result.status == 1){
				$('#store_info').val('');
				var html = '<li>';
					html += '<div class="records_list">';
					html += '<section class="top">';
					html += '<span class="contactor">'+result.data.username+' '+result.data.mobile+'</span>';
					html += '<span class="date">'+result.data.add_time+'</span>';
					html += '</section>';
				 	html += '<p>'+result.data.info+'</p>';
				 	html += '</div>';
				 	html += '</li>';
				$('#stores_log_list').before(html);
				return false;
            } else {
				layer.open({
					content: result.msg,
					style: '',
					time: 1,
					shade:true, //遮罩层 true,false
					anim:true //是否动画弹出 true false
				});
				return false;
            }
		},'json');
	});
	//门店日志信息录入
	$('.get_project_log').click(function(){
		var url = PINER.root + '/?g=weixin&m=project&a=project_log_insert';
		var project_info    = $.trim($('#project_info').val());
		var project_id      = $.trim($('#project_id').val());
		 if(project_info==''){
			layer.open({
				content: '请填写跟进内容',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		 }
		 if(project_id==''){
			layer.open({
				content: '参数有误',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		 }
		 $.post(url,{project_info:project_info,project_id:project_id},function(result){
			if(result.status == 1){
				$('#project_info').val('');
				var html = '<li>';
					html += '<div class="records_list">';
					html += '<section class="top">';
					html += '<span class="contactor">'+result.data.username+' '+result.data.mobile+'</span>';
					html += '<span class="date">'+result.data.add_time+'</span>';
					html += '</section>';
				 	html += '<p>'+result.data.info+'</p>';
				 	html += '</div>';
				 	html += '</li>';
				$('#project_log_list').after(html);
				return false;
            } else {
				layer.open({
					content: result.msg,
					style: '',
					time: 1,
					shade:true, //遮罩层 true,false
					anim:true //是否动画弹出 true false
				});
				return false;
            }
		},'json');
	});

	//项目联系人信息录入
	$('.get_project_contact').click(function(){
		var url = PINER.root + '/?g=weixin&m=project&a=ajax_project_contact_add';
		var thework    = $.trim($('#thework').val());
		var name    = $.trim($('#name').val());
		var tel    = $.trim($('#tel').val());
		var project_contact_id    = $.trim($('#project_contact_id').val());
		var project_contact_cid    = $.trim($('#project_contact_cid').val());
		var th = $(this);
			if(thework==''){
			layer.open({
				content: '请填写联系人职位',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		if(name==''){
			layer.open({
				content: '请填写联系人姓名',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		if(tel==''){
			layer.open({
				content: '请填写联系人电话',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		if(!tel .match(mobile_regex)){
			layer.open({
				content: '手机号码格式不正确',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false; 
	 	} 
		if(project_contact_id==''){
			layer.open({
				content: '参数有误',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		th.removeClass('get_project_contact');
		th.text('正在提交...');
		$.post(url,{thework:thework,name:name,tel:tel,project_contact_id:project_contact_id,project_contact_cid:project_contact_cid},function(result){
			if(result.status == 1){
				layer.open({
					content: result.msg,
					style: '',
					time: 1,
					shade:false, //遮罩层 true,false
					anim:true //是否动画弹出 true false
				});
				window.location.href = PINER.root + '/?g=weixin&m=project&a=project_detail&id='+result.data;
				return false;
            } else {
            	th.addClass('get_project_contact');
		 		th.text('确定');
				layer.open({
					content: result.msg,
					style: '',
					time: 1,
					shade:true, //遮罩层 true,false
					anim:true //是否动画弹出 true false
				});
				return false;
            }
		},'json');
	});
	//门店会员信息录入
	$('.stores_username_add').click(function(){
		
		var url = PINER.root + '/?g=weixin&m=stores&a=ajax_stores_username_add';
		var username    = $.trim($('.username').val());
		var mobile    	= $.trim($('.mobile').val());
		var store_id    = $.trim($('.store_id').val());
		var th = $(this);
		if(username==''){
			layer.open({
				content: '请填写姓名',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		if(mobile==''){
			layer.open({
				content: '请填写手机',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		if(!mobile .match(mobile_regex)){
			layer.open({
				content: '手机号码格式不正确',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false; 
	 	} 
		if(store_id==''){
			layer.open({
				content: '参数有误',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		th.removeClass('stores_username_add');
		th.text('正在提交...');
		$.post(url,{username:username,mobile:mobile,store_id:store_id},function(result){
			if(result.status == 1){
				layer.open({
					content: result.msg,
					style: '',
					time: 1,
					shade:false, //遮罩层 true,false
					anim:true //是否动画弹出 true false
				});
				window.location.href = PINER.root + '/?g=weixin&m=stores&a=store_index&id='+result.data;
				return false;
            } else {
            	th.addClass('stores_username_add');
		 		th.text('确定');
				layer.open({
					content: result.msg,
					style: '',
					time: 1,
					shade:true, //遮罩层 true,false
					anim:true //是否动画弹出 true false
				});
				return false;
            }
		},'json');
	});

	//添加一个联系人信息
	$('#project_contact_add_one').click(function(){
		var project_contact_add_one_num = parseInt($('#project_contact_add_one_num').val());
		var num = 1;
		
		var html = '<li  class="project_contact_one'+project_contact_add_one_num+'">';
			html += '<label>项目联系人</label>';
			html += '<input name="thework" class = "thework several" type="text" placeholder="职位">';
			html += '<input name="name" class = "name several" type="text" placeholder="姓名">';
			html += '<input name="tel" class="tel" type="tel" placeholder="电话">';
			html += '</li>';
		$('#project_contact_add_one_num').val(project_contact_add_one_num+1);
		$(this).prev('li').after(html);
		
		return false;
	});
	//新建项目
	$('.project_contact_add_arr').click(function(){

		var url = PINER.root + '/?g=weixin&m=project&a=ajax_project_contact_add_arr';
		var title    = $.trim($('#title').val());
		var address    = $.trim($('#address').val());
		var thework    = $.trim($('.thework').val());
		var name    = $.trim($('.name').val());
		var tel    = $.trim($('.tel').val());
		var property_cate_id    = $.trim($('#property_cate_id').val());
		var item_price    = $.trim($('#item_price').val());
		var get_city_id    = $.trim($('#get_city_id').val());
		var th = $(this);
		if(title==''){
			layer.open({
				content: '请填写新建项目名称',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		if(address==''){
			layer.open({
				content: '请填写项目地址',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		if(property_cate_id==''){
			layer.open({
				content: '参数有误',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
 		var str_thework = str_name = str_tel= '';
 		var tel_num = tel_pre = name_num = thework_num ='';
 		$(".thework").each(function(){
			str_thework +=$(this).val()+ ',';
			if($(this).val() == ''){
 				thework_num  = 1;
 			}
		});
		if(thework_num != ''){
			layer.open({
				content: '请填写联系人职位',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		$(".name").each(function(){
			str_name +=$(this).val()+ ',';
			if($(this).val() == ''){
 				name_num  = 1;
 			}
		});
		if(name_num != ''){
			layer.open({
				content: '请填写联系人姓名',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}

		$(".tel").each(function(){
 			if($(this).val() == ''){
 				tel_num  =1;
 			}
			str_tel +=$(this).val()+ ',';
			if(!$(this).val().match(mobile_regex)){
				tel_pre = 1;
		 	}
		});
		if(tel_num !=''){
			layer.open({
				content: '请填写联系人电话',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false;
		}
		if(tel_pre != ''){
			layer.open({
				content: '手机号码格式不正确',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false; 
				
		}
		th.removeClass('project_contact_add_arr');
	 	th.text('正在提交...');
		$.post(url,{title:title,address:address,str_thework:str_thework,str_name:str_name,str_tel:str_tel,property_cate_id:property_cate_id,item_price:item_price,get_city_id:get_city_id},function(result){
			if(result.status == 1){
				layer.open({
					content: result.msg,
					style: '',
					time: 1,
					shade:false,
					anim:true
				});
				window.location.href = PINER.root + '/?g=weixin&m=project&a=project_list';
				return false;
            } else {
            	th.addClass('project_contact_add_arr');
		 		th.text('确定');
				layer.open({
					content: result.msg,
					style: '',
					time: 1,
					shade:true,
					anim:true
				});
				return false;
            }
		},'json');
	});
//门店日志信息修改
	$('.J_edit_user').click(function(){
		var url = PINER.root + '/?g=weixin&m=user&a=ajax_edit_user';
		var user_info      = $.trim($('#user_info').val());
		var user_type      = $.trim($('#user_type').val());  
		var mobile_code    = $.trim($('#mobile_code').val()); 
		var th             = $(this);
		if(user_type==3){
		 	var user_info      = $.trim($('#mobile').val());
		 	if(mobile_code==''){
			layer.open({
				content: '验证码不能为空',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false;
		 }
		}
		if(user_type==4){
		 	var user_info      = $.trim($('#get_city_id').val());
		}
		if(user_type==6){
		 	var user_info      = $.trim($('#property_cate_id').val());
		}
		if(user_info==''){
			layer.open({
				content: '信息不能为空',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false;
		 }
		 if(user_type==''){
			layer.open({
				content: '参数有误',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false;
		 }
		 if(user_type==3){
			if(!user_info .match(mobile_regex)){ 
				layer.open({
					content: '手机号码格式不正确',
					style: '',
					time: 1,
					shade:false,
					anim:true
				});
				return false; 
		 	} 
		 }

		 th.removeClass('J_edit_user');
		 th.text('正在提交...');
		 $.post(url,{user_info:user_info,user_type:user_type,mobile_code:mobile_code},function(result){
			if(result.status == 1){
				layer.open({
					content: result.msg,
					style: '',
					time: 1,
					shade:false,
					anim:true
				});
				window.location.href = PINER.root + '/?g=weixin&m=user&a=editmy'
				return false;
            } else {
            	th.addClass('J_edit_user');
		 		th.text('保存');
				layer.open({
					content: result.msg,
					style: '',
					time: 1,
					shade:true,
					anim:true
				});
				window.location.href = PINER.root + '/?g=weixin&m=user&a=editmy'
				return false;
            }
		},'json');
	});
//门店日志信息修改
	$('.edit_store').click(function(){
		var url = PINER.root + '/?g=weixin&m=stores&a=ajax_edit_store';
		var store_info      = $.trim($('.store_info').val());
		var store_id      = $.trim($('#store_id').val()); 
		var store_type      = $.trim($('#store_type').val()); 
		
		 if(store_info==''){
			layer.open({
				content: '信息不能为空',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false;
		 }
		 if(store_type==2){
			if(!store_info .match(mobile_regex)){ 
				layer.open({
					content: '手机号码格式不正确',
					style: '',
					time: 1,
					shade:false,
					anim:true
				});
				return false; 
		 	} 
		 }
		 if(store_id=='' || store_type==''){
			layer.open({
				content: '参数有误',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false;
		 }
		 $.post(url,{store_info:store_info,store_id:store_id,store_type:store_type},function(result){
			if(result.status == 1){
				layer.open({
					content: result.msg,
					style: '',
					time: 1,
					shade:false,
					anim:true
				});
				window.location.href = PINER.root + '/?g=weixin&m=stores&a=store_info&id='+result.data;
				return false;
            } else {
            	window.location.href = PINER.root + '/?g=weixin&m=stores&a=store_info&id='+result.data;
				layer.open({
					content: result.msg,
					style: '',
					time: 1,
					shade:true,
					anim:true
				});
				return false;
            }
		},'json');
	});

	//楼盘报备弹出
	$('.J_loupan_baobei').click(function(){
		var protection = $('#protection').val();
		var html = '<ul class="box_baobei FORM_LISTS">';
		    html += '<li>提交客户信息<button class="btn_cancel" onClick="tc_close();">取消</button></li>';
			html += '<li><span>客户姓名</span><input type="text" name="name" id="name" placeholder="请填写客户姓名" /></li>';
			//if(protection==1){
				html += '<li><span>客户电话</span><input type="tel" name="mobile" id="mobile" placeholder="开发商需要客户手机全号" /></li>';
			//}else{
			//	html += '<li><span>客户电话</span><input type="tel" class="width_half" placeholder="手机前三位" /><input type="tel" class="width_half right"  placeholder="手机后四位" /></li>';
			//}
			html += '<li class="btnbox"><button class="btn_submit J_baobei_add_submit">提交</button></li>';
			html += '</ul>';
		layer.open({
			type: 1,
			content: html,
			shadeClose:false,
			//style: 'width:90%;'
		});									 
	});
	
	//提交报备
	$('.J_baobei_add_submit').live('click',function(){
		var name     = $.trim($('#name').val());
		var mobile   = $.trim($('#mobile').val());
		var property = $.trim($('#property').val());
		var url      = PINER.root + '/?g=weixin&m=baobei&a=add';
		var th       = $(this);
		if(name==''){
			layer.open({
				content: '请输入客户姓名',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false; 
		}
		if(mobile==''){
			layer.open({
				content: '请输入客户电话',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false; 
		}
		if(!name.match(/^[a-zA-Z\u3E00-\u9FA5]+$/)){ 
			layer.open({
				content: '客户姓名只能是中文或英文',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false; 
		} 
		if(!mobile.match(mobile_regex)){ 
			layer.open({
				content: '手机号码填写错误',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false; 
		}
		th.text('正在提交...');
		th.removeClass('J_baobei_add_submit');
		$.post(url,{name:name,mobile:mobile,property:property},function(result){
			if(result.status == 1){
				layer.closeAll();
				//报备成功弹出
				//baobai_success(result.msg);
				layer.open({
					content: result.msg,
					btn: ['确定']
				});
				return false;
            } else {
				layer.closeAll();
				th.addClass('J_baobei_add_submit');
                th.text('提交');
				//baobai_success(result.msg);
				layer.open({
					content: result.msg,
					btn: ['确定']
				});
				return false;
            }
		},'json');
	});
	
	//活动报名弹出
	$('.J_huodong_baoming').click(function(){
		var protection = $('#protection').val();
		var html = '<ul class="box_baobei FORM_LISTS">';
		    html += '<li>活动报名<button class="btn_cancel" onClick="tc_close();">取消</button></li>';
			html += '<li><span>我的姓名</span><input type="text" name="name" id="name" placeholder="姓名" /></li>';
			html += '<li><span>我的电话</span><input type="tel" name="mobile" id="mobile" placeholder="手机号码" /></li>';
			html += '<li class="btnbox"><button class="btn_submit J_baoming_add_submit">提交</button></li>';
			html += '</ul>';
		layer.open({
			type: 1,
			content: html,
			shadeClose:false,
		});									 
	});
	
	//提交报名
	$('.J_baoming_add_submit').live('click',function(){
		var name     = $.trim($('#name').val());
		var mobile   = $.trim($('#mobile').val());
		var pid      = $.trim($('#pid').val());
		var url      = PINER.root + '/?g=weixin&m=loupan&a=huodong_baoming';
		var th       = $(this);
		if(pid==''){
			layer.open({
				content: '请选择所要报名的活动',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false; 
		}
		if(name==''){
			layer.open({
				content: '请输入您的姓名',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false; 
		}
		if(mobile==''){
			layer.open({
				content: '请输入您的电话',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false; 
		}
		if(!name.match(/^[a-zA-Z\u3E00-\u9FA5]+$/)){ 
			layer.open({
				content: '姓名只能是中文或英文',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false; 
		} 
		if(!mobile.match(mobile_regex)){ 
			layer.open({
				content: '手机号码填写错误',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false; 
		}
		th.text('正在提交...');
		th.removeClass('J_baoming_add_submit');
		$.post(url,{name:name,mobile:mobile,pid:pid},function(result){
			if(result.status == 1){
				layer.closeAll();
				layer.open({
					content: result.msg,
					btn: ['确定']
				});
				return false;
            } else {
				th.addClass('J_baoming_add_submit');
                th.text('提交');
				layer.open({
					content: result.msg,
					btn: ['确定']
				});
				return false;
            }
		},'json');
	});
	
	//新建案场
	$('.J_add_case').click(function(){
		var name        = $.trim($('#name').val());
		var city_id     = $.trim($('#get_city_id').val());
		var address     = $.trim($('#address').val());
		var url         = PINER.root + '/?g=weixin&m=case&a=add';
		var th          = $(this);
		if(name==''){
			layer.open({
				content: '请输出案场名称',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false; 
		}
		if(city_id==''){
			layer.open({
				content: '请选择区域板块',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false; 
		}
		if(address==''){
			layer.open({
				content: '请输入项目地址',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false; 
		}
		
		th.text('正在提交...');
		th.removeClass('J_add_case');
		$.post(url,{name:name,city_id:city_id,address:address},function(result){
			if(result.status == 1){
				layer.open({
					content: result.msg,
					btn: ['确认'],
					shadeClose: false,
					yes: function(){
						window.location.href = PINER.root + '/?g=weixin&m=case&a=detail&id='+result.data;
					}
				});
				return false;
            }else{
				th.addClass('J_add_case');
                th.text('确定');
				layer.open({
					content: result.msg,
					btn: ['确定']
				});
				return false;
            }
		},'json');
	});
	
	//添加驻守人员弹出
	$('.J_case_user_html').click(function(){
		var protection = $('#protection').val();
		var html = '<ul class="box_baobei FORM_LISTS">';
		    html += '<li>添加驻守人员<button class="btn_cancel" onClick="tc_close();">取消</button></li>';
			html += '<li><span>我姓名</span><input type="text" name="username" id="username" placeholder="姓名" /></li>';
			html += '<li><span>手机号码</span><input type="tel" name="mobile" id="mobile" placeholder="手机号码" /></li>';
			html += '<li class="btnbox"><button class="btn_submit J_case_user">提交</button></li>';
			html += '</ul>';
		layer.open({
			type: 1,
			content: html,
			shadeClose:false,
		});									 
	});




	//抽奖验证码
	  $('.hongbao_code_s').live('keyup',function(){
	    if($("#mobile_code").val().length == 116){
	    	var url = PINER.root + '/?g=weixin&m=lottery&a=ajax_hongbao';
			var mobile      = $.trim($('#mobile').val());
			var mobile_code    = $.trim($('#mobile_code').val());
		 	if(mobile_code==''){
				layer.open({
					content: '验证码不能为空',
					style: '',
					time: 1,
					shade:false,
					anim:true
				});
				return false;
			 }
			if(mobile==''){
				layer.open({
					content: '手机号码不能为空',
					style: '',
					time: 1,
					shade:false,
					anim:true
				});
				return false;
			 }
			if(!mobile.match(mobile_regex)){
				layer.open({
					content: '手机号码格式不正确',
					style: '',
					time: 1,
					shade:false,
					anim:true
				});
				return false; 
		 	} 
			$.post(url,{mobile:mobile,mobile_code:mobile_code},function(result){
				if(result.status == 1){
					$('.is_disab').val(1);
					$(".lottery_one").attr("class", "btn_tryagain lottery_one"); 
					$(".lottery_one").attr("id", "lottery");
					return false;
	            } else {
	            	$('.is_disab').val(0);
					$(".lottery_one").attr("class", "btn_tryagain lottery_one disabled");
					$(".lottery_one").attr("id", "lottery2");
					//window.location.href = PINER.root + '/?g=weixin&m=user&a=editmy'
					return false;
	            }
			},'json');
	    }
  })
	//添加驻守人员
	$('.J_case_user').live('click',function(){
		var username  = $.trim($('#username').val());
		var mobile    = $.trim($('#mobile').val());
		var stores_id = $.trim($('#stores_id').val());
		var url      = PINER.root + '/?g=weixin&m=case&a=detail';
		var th       = $(this);
		if(stores_id==''){
			layer.open({
				content: '系统参数错误',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false; 
		}
		if(username==''){
			layer.open({
				content: '请输入姓名',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false; 
		}
		if(mobile==''){
			layer.open({
				content: '请输入手机号码',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false; 
		}
		if(!mobile.match(mobile_regex)){ 
			layer.open({
				content: '手机号码格式不正确!',
				style: '',
				time: 1,
				shade:false, //遮罩层 true,false
				anim:true //是否动画弹出 true false
			});
			return false; 
		 } 
		th.text('正在提交...');
		th.removeClass('J_case_user');
		$.post(url,{username:username,mobile:mobile,stores_id:stores_id},function(result){
			if(result.status == 1){
				layer.closeAll();
				layer.open({
					content: result.msg,
					btn: ['确认'],
					shadeClose: false,
					yes: function(){
						window.location.reload();
						return false;
					}
				});
				return false;
            } else {
				th.addClass('J_case_user');
                th.text('提交');
				layer.open({
					content: result.msg,
					btn: ['确定']
				});
				return false;
            }
		},'json');
	});
	
	
	
	
	
	

//Jquery end
})

//关闭弹出
function tc_close(){
	layer.closeAll();
}

//报备成功弹出
function baobai_success(msg){
	var html = '<ul class="box_baobei FORM_LISTS">';
		html += '<li>提交客户信息<button class="btn_cancel" onClick="tc_close()">取消</button></li>';
		html += '<li><p class="txt">'+msg+'</p></li>';
		html += '<li><button class="btn_submit" onClick="tc_close()">确定</button></li>';
		html += '</ul>';
	layer.open({
		type: 1,
		content: html,
	});							
}


function shai_stores_city_pid(){
   if($('#shai_city').val() == ''){
    var url = PINER.root + '/?g=weixin&m=stores&a=shai_city';
    $.post(url,{
      shai_city:$('#city_id').val(),
      shai_type:1,
      },function(result){
      if(result.status == 1){
        $('#shai_city').html(result.data);
      }
    },'json');
    $('#get_city_id').val($('#city_id').val());
    $('#shai_city').html('<option value="">请选择区域</option>');
    $('#shai_ban').parents('li').hide();
  }else{
    var url = PINER.root + '/?g=weixin&m=stores&a=shai_city';
    $.post(url,{shai_city:$('#shai_city').val(),shai_type:2},function(result){
      if(result.status == 1){
        $('#shai_ban').parents('li').show();
        $('#shai_ban').html(result.data);
      }else{
        $('#shai_ban').parents('li').hide();
      }
     $('#shai_ban').val('请选择版块');
    },'json');
    $('#get_city_id').val($('#shai_city').val());
  }
}

function city_stores_id_pid(){
 
  if($('#city_id').val() == ''){
     $('#shai_city').parents('li').hide();
     $('#shai_ban').parents('li').hide();
  }else{
    $('#shai_city').parents('li').show();
    var url = PINER.root + '/?g=weixin&m=stores&a=shai_city';
    $.post(url,{
      shai_city:$('#city_id').val(),
      shai_type:1,
      },function(result){
      if(result.status == 1){
        $('#shai_city').html(result.data);
      }else{
        $('#shai_city').parents('li').hide();
      }
    },'json');
  }
  $('#get_city_id').val($('#city_id').val());
}


function shai_stores_ban_pid(){
  if($('#shai_ban').val() == ''){
    var url = PINER.root + '/?g=weixin&m=stores&a=shai_city';
    $.post(url,{
      shai_city:$('#shai_city').val(),
      shai_type:3,
      },function(result){
      if(result.status == 1){
        $('#shai_ban').html(result.data);
      }
    },'json');

    if($('#shai_city').val()){
      $('#get_city_id').val($('#shai_city').val());
    }else if($('#city_id').val()){
        $('#get_city_id').val($('#city_id').val());
    }
    $('#shai_ban').html('<option value="">请选择板块</option>');
  }else{
    $('#get_city_id').val($('#shai_ban').val());
  }
}
/*
*发送手机验证码
*@param send_url
*@param send_code
*Author CH-L
*/
function get_mobile_code(){
	var mobile = $.trim($('#mobile').val());
	if(mobile==''){
		layer.open({
			content: '请输入手机号码',
			style: '',
			time: 1,
			shade:false,
			anim:true
		});
		return false;
	}
	 if(!mobile.match(mobile_regex)){ 
		layer.open({
			content: '手机号码格式不正确！请重新输入！',
			style: '',
			time: 1,
			shade:false,
			anim:true
		});
		return false; 
	 } 
	$('#zphone').val('请等待...');
	$.post(send_url, {mobile:mobile,send_code:send_code}, function(msg) {
		if(msg=='提交成功'){
			RemainTime();
		}else{
			layer.open({
				content: jQuery.trim(unescape(msg)),
				style: '',
				time: 1,
				shade:true,
				anim:true
			});
		}
	});
};
/*
*手机验证码倒计时
*@param iTime
*@param Account
*Author CH-L
*/
function RemainTime(){
	document.getElementById('zphone').disabled = true;
	var iSecond,sSecond="",sTime="";
	if (iTime >= 0){
		iSecond = parseInt(iTime%60);
		iMinute = parseInt(iTime/60)
		if (iSecond >= 0){
			if(iMinute>0){
				sSecond = iMinute + "分" + iSecond + "秒";
			}else{
				sSecond =  iSecond + "秒后重新发送";
			}
		}
		sTime=sSecond;
		if(iTime==0){
			clearTimeout(Account);
			sTime='获取手机验证码';
			iTime = 59;
			document.getElementById('zphone').disabled = false;
		}else{
			Account = setTimeout("RemainTime()",1000);
			iTime=iTime-1;
		}
	}else{
		sTime='没有倒计时';
	}
	document.getElementById('zphone').value = sTime;
}


	//收藏楼盘
	$('.J_favorite').live('click',function(){
		var pid = $('#favorite').val();
		var uid = $('#uid').val();	
		var count = parseInt($('.aR i').text())+1;
		var return_url = $.trim($('#return_url').val());
		var url = PINER.root + '/?g=weixin&m=loupan&a=favorite';
		var th = $(this);
		if(uid==''){
			layer.open({
				content: '登录之后才能收藏',
				btn: ['确认', '取消'],
				shadeClose: false,
				yes: function(){
					window.location.href=PINER.root + '/?g=weixin&m=user&a=login&url='+return_url; 
					return false;
				}
			});
			return false;
		}	
		if(pid==''){
			layer.open({
				content: '参数出错',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false;
		}
		$.post(url,{pid:pid},function(result){
			if(result.status == 1){
				$('.aR i').text(count);
				th.attr('class','gotolist nobg J_favorites off');
				th.html('取消收藏');
				layer.open({
					content: result.msg,
					style: '',
					time: 1,
					shade:false,
					anim:true
				});
				return false;
			} else {
				layer.open({
					content: result.msg,
					style: '',
					time: 1,
					shade:true,
					anim:true
				});
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
			layer.open({
				content: '参数出错',
				style: '',
				time: 1,
				shade:false,
				anim:true
			});
			return false;
		}
		$.post(url,{pid:pid},function(result){
			if(result.status == 1){
			   th.attr('class','gotolist nobg J_favorite');
			   $('.aR i').text(count);
			   th.html('收藏楼盘');
			    layer.open({
					content: result.msg,
					style: '',
					time: 1,
					shade:false,
					anim:true
				});
				return false;
			} else {
				layer.open({
					content: result.msg,
					style: '',
					time: 1,
					shade:true,
					anim:true
				});
			   	return false;
			}
		},'json');
	});
	

//跳转
function MM_jumpMenu(targ,selObj,restore){ 
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}

/*
*正则
*/
//验证手机
var mobile_regex = /^13[0-9]{1}[0-9]{8}$|14[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[0123456789]{1}[0-9]{8}$|14[0-9]{1}[0-9]{8}$|17[0123456789]{1}[0-9]{8}$/;
var num4= /^\+?[1-9][0-9]*$/;//正数（正整数 + 0）
var email_regex= /^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/;//邮箱
//var money_regex = /^[1-9]\d{0,5}(\.\d{1,2})?/;//金额，支持两位小数，不可以为0
var money_regex = /^[1-9]\d{0,5}(\.\d{1,2})?$/;//金额，支持两位小数，不可以为0
var num6 = /^[1-9-.]\d*$/; //不可为0的整数
var num7 = /^[1-9]\d*.\d*|0.\d*[1-9]\d*$/; //不可为0的整数
//验证身份证号码
var aCity={11:"北京",12:"天津",13:"河北",14:"山西",15:"内蒙古",21:"辽宁",22:"吉林",23:"黑龙江 ",31:"上海",32:"江苏",33:"浙江",34:"安徽",35:"福建",36:"江西",37:"山东",41:"河南",42:"湖北 ",43:"湖南",44:"广东",45:"广西",46:"海南",50:"重庆",51:"四川",52:"贵州",53:"云南",54:"西藏 ",61:"陕西",62:"甘肃",63:"青海",64:"宁夏",65:"新疆",71:"台湾",81:"香港",82:"澳门",91:"国外 "}