$(function() {
   var jiedian = $('.jiedian').length-1;
   //alert(jiedian);
		//上传图片
    var img_uploader = new qq.FileUploaderBasic({
        allowedExtensions: ['jpg','gif','jpeg','png','bmp','pdg'],
        button: document.getElementById('J_upload_img'+jiedian),
        multiple: false,
        action: uploae_url,
        inputName: 'img',
        forceMultipart: true, //用$_FILES
        messages: {
            typeError: lang.upload_type_error,
            sizeError: lang.upload_size_error,
            minSizeError: lang.upload_minsize_error,
            emptyError: lang.upload_empty_error,
            noFilesError: lang.upload_nofile_error,
            onLeave: lang.upload_onLeave
        },
        showMessage: function(message){
            $.pinphp.tip({content:message, icon:'error'});
        },
        onSubmit: function(id, fileName){
            $('#J_upload_img'+jiedian).addClass('btn_disabled').find('span').text(lang.uploading);
        },
        onComplete: function(id, fileName, result){
            $('#J_upload_img'+jiedian).removeClass('btn_disabled').find('span').text(lang.upload);
            if(result.status == '1'){
            	$('#J_upload_img'+jiedian).hide();
                $('#J_img'+jiedian).val(result.data);
                $('#J_img'+jiedian).blur();
                $('#img_data'+jiedian).html('<ul><li><img src="'+IMG_URL+result.data+'" width="100"></li><li><a href="javascript:;" class="del_img_js'+jiedian+'">删除</a></li></ul>');
            } else {
                $.pinphp.tip({content:result.msg, icon:'error'});
            }
        }
    }); 
	
	//删除图片
	$('.del_img_js'+jiedian).live('click',function(){
		var img_path = $(this).parents('.img_border').prev('input').val();
		var delimg_url = del_img_url;
		$.post(delimg_url,{img_path:img_path},function(result){
			if(result.status == 1){
				$('#img_data'+jiedian).html('');
				$('#J_img'+jiedian).val('');
				$('#J_upload_img'+jiedian).show();
				$('#J_img'+jiedian).focus();
				return false;
            } else {
                $.pinphp.tip({content:result.msg, icon:'error'});
				return false;
            }
		},'json');
		return false;
	});
});