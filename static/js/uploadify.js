/*
** uploadify 图片批量上传
*/
$(function(){
	//图片批量上传
	$('#file_upload').uploadify({
		'formData'     : {
			'timestamp' : timestamp,    //时间
			'token'     : token,		//加密字段
			'thumbMaxWidth': thumbMaxWidth,
			'thumbSuffix' : thumbSuffix
		},
		'fileTypeDesc' : 'Image Files',					//类型描述
		//'removeCompleted' : false,    //是否自动消失
		'fileTypeExts' : '*.gif; *.jpg; *.png',		//允许类型
		'fileExt': '*.jpg;*.jpeg;*.gif;*.png',//可上传的文件类型
		'fileSizeLimit' : '3MB',					//允许上传最大值
		'swf'      : static+'/css/default/flash/uploadify.swf',	//加载swf
		'uploader' : uploader,					//上传路径
		'buttonCursor': 'hand',
		'buttonText' :'文件上传',					//按钮的文字
		'buttonImage': static+'/css/default/images/chuan.gif',
		'multi':true,        //一次选择多张
		'simUploadLimit' :3, //并发上传数据
		//'queueSizeLimit' :8, //每次最多选择文件个数
		'uploadLimit': uploadLimit,//上传最大文件个数
		'height': 29,
		'width': 85,
		
		//成功上传返回
		'onUploadSuccess' : function(file, data, response) {
		var n=parseInt(Math.random()*100);//100以内的随机数
		//alert(n+data);
		//插入到image标签内，显示图片的缩略图
		var thumb = thumbSuffix.split(",");
		var name = data.split(".");
		var J_img_up_len = $('#uploadLimit').val();
		var src = 'data/upload/property/'+data+'';
		
		//alert(uploadLimit);<a class="J_feng" href="javascript:;">设为封面</a>&nbsp;&nbsp;&nbsp;&nbsp;
		$('#upload_images').append('<ul><li id="96" class="photo"><input type="hidden" value="'+data+'" name="picarr[]" class="picarr"><img src="'+PINER.root+'/data/upload/property/'+name[0]+''+thumb[0]+'.'+name[1]+'"/><div class="del J_none"><a class="J_img_del" href="javascript:;">删除</a></div></li>'
					   +'<li>户型名称<input class="up_input" name="name[]" type="text" /></li>'
					   +'<li>户型结构<input class="up_input_mini" name="fangxinshi[]" type="text" />室<input class="up_input_mini" name="fangxinting[]" type="text" />厅<input class="up_input_mini" name="fangxinwei[]" type="text" />卫</li>'
					   +'<li>户型面积<input class="up_input" name="huxinmianji[]" type="text" /></li><li>户型买点<textarea name="maidian[]"></textarea></li>'
					   +'<li>在售状态<select  name="sell[]"><option value="1">待售</option><option value="2">在售</option><option value="3">售完</option></select></li>'
					   +'<li>主力户型<select  name="huxinstatus[]"><option value="1">否</option><option value="2">是</option><</select></li>'
					   +'<li>序号<input class="up_input_mini" name="fangordid[]" type="text" /></ul>');
			var len = $(".image_up ul").length;
			if(len==1){
				$('.J_feng').text('封面').css({color:"#FF9900"});
				$('#imgid').val(n);
				$('#imgid').blur();
				//$('.J_feng').removeClass("J_feng");
			}
		}
	});		   
});