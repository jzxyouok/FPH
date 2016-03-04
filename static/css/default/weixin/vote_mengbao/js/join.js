//报名表单
$(function(){

	$.validator.addMethod( "telePhone",function (value,element){         // 自定义验证
	       var telePhone= /^[1][358]\d{9}$/;
	       return this.optional(element ) || telePhone.test(value);
	});
	$.validator.addMethod( "ages",function (value,element){         // 自定义验证
	       var ager= /^\d{1,2}$/;
	       return this.optional(element ) || ager.test(value);
	});

	//表单验证
	$("#joinForm").validate({
		rules:{
			user:{//最大长度为8个字符
				required:true,
				maxlength:8
			},
			age:{//年龄必须为整数
				required:true,
				ages:true
			},
			tel:{
				required:true,
				telePhone:true //验证手机格式
			},
			intro:{//最多输入100字
				maxlength:100
			},
			imgLoad:{
				required:true
			}
		},
		messages:{
			user:{
				required:"姓名不能为空",
				maxlength:"姓名最多输入8个字符!"
			},
			age:{
				required:"年龄不能为空",
				ages:"请输入正确的年龄"
			},
			tel:{
				required:"请填写手机号!",
				telePhone:"请填写正确的手机号码!"
			},
			intro:{
				maxlength:"最多输入100字,您输入的内容已经超出了最大长度"
			},
			imgLoad:{
				required:"请至少选择一张图片!"
			}
		}
	});

	//图片上传
	
	//这里的图片以数据流的形式存在,设置的逻辑是上传有图片预览
	var totalNumber=5;//上传总数
	var uploadnums =0;
	$("#totalLoadNumber").text( totalNumber );

	$("#imgLoad").on('click',function(){
		if( totalNumber<=0 ){
			alert('已经达到上传上限');
			return false;
		}
	});

	$("#imgLoad").on('change',function(e){
		var imgRe=/.(jpg)|(bmp)|(jpeg)|(png)$/ig;
		if( !imgRe.test( $("#imgLoad").val() )  && $("#imgLoad").val() !=''){
				alert('请上传.jpg、.bmp或.png格式的图片!');
				$("#imgLoad").val('');
				return false;
		}
		else{

			var f=this.files[0];
			if(f){
				var fd=new FileReader();
				fd.readAsDataURL(f);
				//图片成功加载
				fd.onload=function(ev){
					ev= ev || window.event;
					var img=new Image();
					img.src=ev.target.result;
					$(".load-img-wrap").append(img);
					uploadnums++;
					$('#imgs_'+uploadnums).val(ev.target.result);
					totalNumber--;
					$("#totalLoadNumber").text( totalNumber );

				}
			}
		}
	});

	$(".load-img-wrap").delegate("img",'click',function(){

		if(confirm("是否删除这张图片?")){
			$(this).remove();
			$('#imgs_'+uploadnums).val('');
			totalNumber++;
			uploadnums--;
			if(totalNumber >=5){
				totalNumber=5;
			}
			if(uploadnums >=0){
				uploadnums=0;
			}
			$("#totalLoadNumber").text( totalNumber );
		}

	});
});