/*
** uploadify 图片批量上传
*/
$(function(){

	for (var i=1;i<=6;i++) {
		//图片批量上传
		$('#file_upload'+i).uploadify({
			'formData': {
				'timestamp': timestamp,    //时间
				'token': token,		//加密字段
				'pid': pid,
				'status': i
			},
			'fileTypeDesc': 'Image Files',				//类型描述
			//'removeCompleted' : true,    //是否自动消失
			//'removeTimeout' : 1,
			'fileTypeExts': '*.gif; *.jpg; *.png',		//允许类型
			'fileExt': '*.jpg;*.jpeg;*.gif;*.png',//可上传的文件类型
			'fileSizeLimit': '3MB',					//允许上传最大值
			'swf': static + '/css/admin/bgimg/uploadify.swf',	//加载swf
			'uploader': uploader,					//上传路径
			'buttonCursor': 'hand',
			'buttonText': '文件上传',					//按钮的文字
			'buttonImage': static + '/css/admin/bgimg/chuan.gif',
			'multi': true,        //一次选择多张
			'simUploadLimit': 3, //并发上传数据
			'queueSizeLimit' :8, //每次最多选择文件个数
			'uploadLimit': uploadLimit,//上传最大文件个数
			'height': 29,
			'width': 85,

			//成功上传返回
			'onUploadSuccess': function (file, data, response) {
				//alert(data);return false;
				if(typeof data=='string'){
					var result=JSON.parse(data);
				}else if(typeof data=='object'){
					var result=data;
				}
				if(result.status == '1'){
					var html = '';
					html += '<li id="li_img'+result.data['id']+'"><label><input type="radio" name="focus"  onclick="setfocus('+result.data['id']+',{$id});">&nbsp;焦点</label>';
					html += '&nbsp;&nbsp;&nbsp;'
					html += '<label><input id="type'+result.data['id']+'" type="checkbox"  onclick="checkboxtype('+result.data['id']+');"  value="1" >';
					html += '&nbsp;滚屏</label>';
					html +='<div class="img"><img width="100%" height="100%" src="'+result.data['name']+'"></div><span class="name">';
					html +='<span class="tdedit" id="sp_imgtitle'+result.data['id']+'" onclick="imgtitle('+result.data['id']+');"></span>';
					html +='<input style="display:none;" type="text" value="" onblur="uptitle('+result.data['id']+');" size="15" id="inp_imgtitle'+result.data['id']+'">';
					html +='</span><div class="op"> <a href="javascript:;"  onclick="delimg('+result.data['id']+','+result.data['pid']+');">删除</a> </div></li>';
					$('#ui_img'+result.data['status']).append(html);
				}else{
					$.pinphp.tip({content:result.data.msg, icon:'error'});
				}
			}
		});
	}
});